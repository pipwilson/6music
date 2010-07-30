<?php

class Amazon {
    public $key; // https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key
    public $secret; // https://aws-portal.amazon.com/gp/aws/developer/account/index.html?action=access-key
    public $tag; // https://affiliate-program.amazon.com/

    private $host = 'ecs.amazonaws.com';
    private $path = '/onca/xml';

    function sign($params){
      uksort($params, 'strnatcmp');
      foreach ($params as $key => &$value)
        $value = $key . '=' . rawurlencode($value); // http_build_query uses urlencode rather than rawurlencode

      return base64_encode(hash_hmac('sha256', implode("\n", array('GET', $this->host, $this->path, implode('&', $params))), $this->secret, TRUE));
    }

    function lookup($query) {
      if (is_array($query))
        $query = implode(' - ', array($request['artist'], $request['album'], $request['track']));

      $params = array(
        'Service' => 'AWSECommerceService',
        'Version' => '2009-10-01',
        'Timestamp' => date(DATE_ISO8601),
        'AWSAccessKeyId' => $this->key,
        'AssociateTag' => $this->tag,
        'Operation' => 'ItemSearch',
        'SearchIndex' => 'MP3Downloads',
        'ResponseGroup' => 'ItemAttributes,Images',
        'Keywords' => $query,
        );

      $params['Signature'] = $this->sign($params);
      $xml = simplexml_load_file('http://' . $this->host . $this->path . '?' . http_build_query($params));

      if (!is_object($xml) || (string) $xml->Items->Request->IsValid != 'True')
        return array();

      $items = array();
      if (!empty($xml->Items->Item)){
        foreach ($xml->Items->Item as $item){
          $asin = (string) $item->ASIN;
          $attr = $item->ItemAttributes;

          $meta = array(
            'artist' => (string) $attr->Creator,
            'track' => (string) $attr->Title,
            'duration' => (string) $attr->RunningTime,
            'trackno' => (string) $attr->TrackSequence,
            'sample' => 'http://www.amazon.com/gp/dmusic/get_sample_url.html?ASIN=' . $asin,
            'info' => (string) $item->DetailPageURL,
            );

          foreach (array('LargeImage', 'MediumImage', 'SmallImage') as $image){
            if (isset($item->{$image})){
              $meta['image'] = (string) $item->{$image}->URL;
              break;
            }
          }

          $items[] = $meta;
        }
      }
      return $items;
    }
}

