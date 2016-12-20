<?php  namespace App\Services;

/**
* Interact with SAT
*/
class SAT
{

    public function request()
    {

    }

    public function download()
    {
        header('Content-type: text/html; charset=utf-8');

        $filename = 'ejemplo.txt';

        $jsonText = file_get_contents($filename);

        $json = json_decode($jsonText);

        if (json_last_error() != JSON_ERROR_NONE)
        {
          dd("Error en json");
        }

        $json->{"Contribuyente"}->{'Rfc'} = 'CUHC901208KQ8';
        $json->{"Contribuyente"}->{'ClaveCiec'} = '11235813';

        $post['data'] = json_encode($json);
        $postdata = http_build_query($post);

        $server = 'http://www.descargarcfdi.com/descarga';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $server);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        dd($respuesta);
    }

    public function checkRequest()
    {
        
    }
}
