<?php namespace App\Services;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\File;


class ZIP
{
    protected $facturas;

    function __construct($facturas)
    {
        $this->facturas = $facturas;
    }


    public function zipFiles($mes, $rfc)
    {
        if (File::exists($rfc . '_' . $mes . '.zip'))
        {
            unlink($rfc . '_' . $mes . '.zip');
        }
        $zipper = new \Chumper\Zipper\Zipper;
        //array of paths files
        $files = $this->facturas->lists('ruta')->toArray();
        foreach ($files as $key => $file) {
            $files[$key] = 'facturas_clientes/' . $files[$key];
        }
        $zip = Zipper::make($rfc . '_' . $mes . '.zip')->add($files);
        $zip->close();
        return response()->download(public_path($rfc . '_' . $mes . '.zip'));
    }
}