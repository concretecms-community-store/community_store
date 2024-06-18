<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Report;

use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvReportExporter
{
    /**
     * @var \DateTimeZone
     */
    protected $filename;
    protected $header;
    protected $rows;

    /**
     * Initialize the instance.
     *
     * @param Writer $writer
     * @param UserCategory $userCategory
     * @param Date $dateService
     */
    public function __construct($filename, Array $header, Array $rows)
    {
        // if empty filename give it a default
        $filename = empty($filename) ? 'report_' . date('Y-m-d') . ".csv" : $filename;
        // add the 'csv' extension if not already present
        $filename = strtolower(substr(strrchr($filename, '.'), 1)) === 'csv' ? $filename : $filename . '.csv';

        $this->filename = $filename;
        $this->header = $header;
        $this->rows = $rows;
    }

    public function getCsv()
    {
        $now = gmdate("D, d M Y H:i:s");
        $expire = gmdate("D, d M Y H:i:s", strtotime("+1 day"));

        $httpHeaders = [
            'Expires' => $expire . ' GMT',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            'Last-Modified' => $now . ' GMT',
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $this->filename . '"',
            'Content-Transfer-Encoding' => 'binary'
        ];

        $response = new StreamedResponse(
            function () {
                $writer = Writer::createFromPath('php://output', 'w');
                if (count($this->header)) {
                    $writer->insertOne($this->header);
                }
                if (count($this->rows)) {
                    $writer->insertAll($this->rows);
                }
            },
            200,
            $httpHeaders
        );

        $response->send();

        exit();
    }
}
