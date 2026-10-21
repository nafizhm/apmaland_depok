<?php

namespace Tests\Feature;

use App\Services\SiteplanPrint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteplanPrintTest extends TestCase
{
    private string $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 800 400"><style>.lot {fill:#315f31;}</style><path class="lot" d="M10 10 L200 10 L200 200 Z"/><text x="40" y="80">A1</text></svg>';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kavling');
        });
        Schema::create('konfigurasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan');
        });
        DB::table('lokasi_kavling')->insert(['id' => 1, 'nama_kavling' => 'Lokasi Satu']);
        DB::table('konfigurasi')->insert(['nama_perusahaan' => 'Apmaland']);
    }

    public function test_pdf_is_generated_locally_as_a_single_landscape_page(): void
    {
        $response = app(SiteplanPrint::class)->download($this->svg, 1, 'Air', 'pdf');
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertSame('inline; filename="siteplan-air-lokasi-satu.pdf"', $response->headers->get('Content-Disposition'));
        $parser = new \setasign\Fpdi\Tcpdf\Fpdi();
        $pages = $parser->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($response->getContent()));
        $this->assertSame(1, $pages);
        $size = $parser->getTemplateSize($parser->importPage(1));
        $this->assertGreaterThan($size['height'], $size['width']);
        Http::assertNothingSent();
    }

    public function test_jpg_page_contains_the_local_canvas_export_and_selected_type(): void
    {
        $response = app(SiteplanPrint::class)->download($this->svg, 1, 'BPHTB dan SSP', 'jpg');
        $html = $response->getContent();
        $this->assertStringContainsString('Siteplan BPHTB dan SSP - Lokasi Satu', $html);
        $this->assertStringContainsString("'image/jpeg'", $html);
        $this->assertStringContainsString('siteplan-bphtb-dan-ssp-lokasi-satu.jpg', $html);
        Http::assertNothingSent();
    }

    public function test_svg_keeps_its_aspect_ratio_and_removes_active_and_remote_content(): void
    {
        [$svg, $width, $height] = app(SiteplanPrint::class)->prepareSvg(str_replace('</svg>', '<script>alert(1)</script><image href="https://example.com/test.jpg"/><path onclick="alert(1)"/></svg>', $this->svg));
        $this->assertSame(800.0, $width);
        $this->assertSame(400.0, $height);
        $this->assertStringNotContainsString('<script', $svg);
        $this->assertStringNotContainsString('https://', $svg);
        $this->assertStringNotContainsString('onclick', $svg);
        $this->assertStringContainsString('fill:#315f31', $svg);
    }

    public function test_pdf_applies_class_colors_and_label_sizes_without_overriding_unit_status_colors(): void
    {
        $source = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40000 43000"><defs><style><![CDATA[
            .fil5 {fill:#757475;fill-rule:nonzero}
            .fil4 {fill:#332C2B}
            .fnt1 {font-weight:normal;font-size:449.85px;font-family:\'Bahnschrift\'}
        ]]></style></defs><polygon class="fil5" points="0,0 100,0 100,100" style="fill:#ff5454"/>
        <g transform="matrix(0.529919 0.848048 -0.848048 0.529919 27522.6 9672.69)">
        <text x="20000" y="21500" class="fil4 fnt1">A-1</text></g></svg>';
        $document = new \DOMDocument();
        $document->loadXML(app(SiteplanPrint::class)->preparePdfSvg($source));
        $polygon = $document->getElementsByTagName('polygon')->item(0);
        $this->assertSame('#ff5454', $polygon->getAttribute('fill'));
        $text = $document->getElementsByTagName('text')->item(0);
        $this->assertSame('#332C2B', $text->getAttribute('fill'));
        $this->assertSame('449.85px', $text->getAttribute('font-size'));
        $this->assertSame('helvetica', $text->getAttribute('font-family'));
        $this->assertSame('A-1', $text->textContent);
        $this->assertSame('matrix(0.529919 0.848048 -0.848048 0.529919 27522.6 9672.69)', $text->parentNode->getAttribute('transform'));

        $pdf = new \TCPDF();
        $pdf->SetCompression(false);
        $pdf->AddPage();
        $pdf->ImageSVG('@' . $document->saveXML($document->documentElement), 10, 10, 180, 193.5);
        $output = $pdf->Output('regression.pdf', 'S');
        $this->assertStringContainsString('[(A-1)] TJ', $output);
        $this->assertStringContainsString('449.850000 Tf', $output);
        $this->assertStringContainsString('1.000000 0.329412 0.329412 rg', $output);
    }
}
