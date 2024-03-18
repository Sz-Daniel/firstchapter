<?php

function apiGetCall($param)
{
    $url = "https://jsonplaceholder.typicode.com/".$param;
    return json_decode(file_get_contents($url),true);
}


    // Full process description on APIcUrlCall
    function APIcUrlCall($url, $type = "GET", $body = null)
    {
        /**Universal usage for every kind of API call with cURL
         * url should be full format with query
         * set for all type of CRUD - GET-POST-PUT-PATCH-DELETE
         * body need to be assoc_array
         * Any kind of error will logged to 'log' database
         * cURL not support try-catch error handling => data testing and early return with null as error
         */

        // curl session open and setup the default cURL options with `$url`
        $ch = curl_init();
        $options = array(
            //url set 
            CURLOPT_URL => $url,
            //in default the result would be on the page directly
            //with CURLOPT_RETURNTRANSFER, the resoult will get from curl_exec($ch)
            CURLOPT_RETURNTRANSFER => true,
            //in that case when the API couldn't elérhető, timeout set for 30sec
            CURLOPT_TIMEOUT => 30,
            //Result type set
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        );
        curl_setopt_array($ch,$options);
        /** Inline version of curl_setopt_array
         * curl_setopt($ch, CURLOPT_URL, $url);
         * curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         * curl_setopt($ch, CURLOPT_TIMEOUT, 30);
         * curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
         */
        
        // CRUD nak megfelelő beállítások létrehozása `$type` alapján
        if ($type === "GET" || $type === "DELETE") {
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $type);
        } else {
            if ($type === "POST"){
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST");
            } elseif($type === "PUT"){
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            } elseif($type === "PATCH"){
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            } else {
                logJS("Wrong type:".$type.$url);
                curl_close($ch);
                return null;
            }

            //Early return with error handle when the param doesn't have body for POST - PATCH - PUT 
            if ($body === null) {
                logJS("Missing body:".$type.$url);
                curl_close($ch);
                return null;
            }

            //Attach the body data
            $post_data = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        //execute the call
        $curl_response = curl_exec($ch);

        //any problem with execute handle here (http error handle below)
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }

        /**
         * with execute cannot get the http error
         * for this need curl_getinfo and test for anything above 400
         * Error will in 'log' database
         */
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response_code >= 400) {
            logJS("HTTP Error: " . $response_code);
            curl_close($ch);
            return null;
        }

        /**
         * if any early reaturn error handly doesnt procced at this point,
         * the result is ok and will decoded from json and curl session closed
         */
        $response_data = json_decode( $curl_response, true);
        curl_close($ch);
        return $response_data;
    }
    function APIGetUsers()
    {
        $url = "https://fakestoreapi.com/users";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);

        $curl_response=curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $curl_response_info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($curl_response_info >= 400) {
            logJS("HTTP Error: " . $curl_response_info);
            curl_close($ch);
            return null;
        }

      $data = json_decode( $curl_response, true);
      curl_close($ch);
      return $data;

    }
    function APIGetUserById($id)
    {
        $url = "https://fakestoreapi.com/users/".$id;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT,30);

        $curl_response=curl_exec($ch);
        if($curl_response === "null")
        {
            logJS("Person doesent exist");
            curl_close($ch);
            return null;
        }
        
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $curl_response_info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($curl_response_info >= 400) {
            logJS("HTTP Error: " . $curl_response_info);
            curl_close($ch);
            return null;
        }

      $data = json_decode( $curl_response, true);
      curl_close($ch);
      return $data;
    }
    function APIEditUserById($id, $body, $type = false) 
    {
        //type 0 PUT - 1 PATCH
        $post_data = json_encode($body);

        $url = "https://fakestoreapi.com/users/".$id;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        if ((bool)$type){
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        } else{
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);


        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;

    }
    function APICreateUser($body) 
    {
        $post_data = json_encode($body);
        $url = "https://fakestoreapi.com/users/";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);


        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;

    }
    function curl_GET($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  
        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;
    }
    function curl_POST($url ,$body)
    {
        $post_data = json_encode($body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        
        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;
    }
    function curl_PATCH($url ,$body)
    {
        $post_data = json_encode($body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  
        
        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;
    }
    function curl_PUT($url ,$body)
    {
        $post_data = json_encode($body);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  
        
        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;
    }
    function curl_DELETE($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
  
        $curl_response = curl_exec($ch);
        if($curl_response === false)
        {
            logJS(curl_errno($ch)." - ".curl_error($ch));
            curl_close($ch);
            return null;
        }
  
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode >= 400) {
            logJS("HTTP Error: " . $responseCode);
            curl_close($ch);
            return null;
        }

        $data = json_decode( $curl_response, true);
        curl_close($ch);
        return $data;
    }

    //For error testing
    function curl_error_test($url) 
    {
      /**
       * curl_error_test("http://expamle.com");          // CURL Error: Could not resolve host: expamle.com
       * curl_error_test("http://example.com/whatever"); // HTTP Error: 404
       * curl_error_test("http://example.com");          // No CURL or HTTP Error
       */
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  
      $responseBody = curl_exec($ch);
      /*
       * if curl_exec failed then
       * $responseBody is false
       * curl_errno() returns non-zero number
       * curl_error() returns non-empty string
       * which one to use is up too you
       */
      if ($responseBody === false) {
          return "CURL Error: " . curl_error($ch);
      }
  
      $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($responseCode >= 400) {
          return "HTTP Error: " . $responseCode;
      }
  
      return "No CURL or HTTP Error";
    }