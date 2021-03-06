<?php
/**
 * Php proxy to access OpenStreetMap services.
 *
 * @author    3liz
 * @copyright 2011-2019 3liz
 *
 * @see      http://3liz.com
 *
 * @license Mozilla Public License : http://www.mozilla.org/MPL/
 */
class banCtrl extends jController
{
    /**
     * Query the Base Adresse National API.
     *
     * @urlparam text $query A query on BAN object
     * @urlparam text $bbox A bounding box in EPSG:4326
     *
     * @return jResponseBinary JSON content
     */
    public function search()
    {
        $rep = $this->getResponse('binary');
        $rep->outputFileName = 'ban.json';
        $rep->mimeType = 'application/json';

        $query = $this->param('query');
        if (!$query) {
            $rep->content = '[]';

            return $rep;
        }

        $url = 'https://api-adresse.data.gouv.fr/search/?';
        $params = array(
            'q' => $query,
            'format' => 'json',
        );
        $bbox = $this->param('bbox');
        if (preg_match('/\d+(\.\d+)?,\d+(\.\d+)?,\d+(\.\d+)?,\d+(\.\d+)?/', $bbox)) {
            $params['viewbox'] = $bbox;
        }

        $url .= http_build_query($params);
        list($content, $mime, $code) = lizmapProxy::getRemoteData($url, array(
            'method' => 'get',
            'referer' => jUrl::getFull('view~default:index'),
        ));

        $var = json_decode($content);
        $obj = $var->features;
        $licence = 'Data © '.$obj->attribution.', '.$obj->licence;
        $res = array();
        $res['search_name'] = 'BAN';
        $res['layer_name'] = 'ban';
        $res['srid'] = 'EPSG:4326';
        $res['features'] = array();
        foreach($obj as $key){
            /*
            $lat = $key->geometry->coordinates[1];
            $lon = $key->geometry->coordinates[0];
            $boundingbox = [strval($lat),strval($lat),strval($lon),strval($lon)];
            $display_name = $key->properties->label.', '.$key->properties->context;
            $return["licence"]= $licence;
            $return["display_name"]= $display_name;
            $return["lat"] = strval($lat);
            $return["lon"]= strval($lat);
            $return["boundingbox"]= $boundingbox;
            array_push($res,$return);
            */
            $lat = $key->geometry->coordinates[1];
            $lon = $key->geometry->coordinates[0];
            $display_name = $key->properties->label.', '.$key->properties->context;

            $d = array();
            $d['label'] = $display_name;
            $d['geometry'] = 'POINT('.$lon.' '.$lat.')';
            array_push($res['features'],$d);
        }

        $rep->content = json_encode(array('ban' => $res));

        return $rep;
    }
}
