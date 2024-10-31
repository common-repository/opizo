<?php
/**
 *
 * @link       http://opizo.com
 * @since      1.0.0
 *
 * @package    Opizo
 * @subpackage Opizo/includes
 */

/**
 * @since      1.0.0
 * @package    Opizo
 * @subpackage Opizo/includes
 * @author     Opizo <opizo.com@gmail.com>
 */
class OpizoApi
{
    /**
     *
     * @var OpizoApi
     */
    private static $instance = null;
    private $api_endpoint = 'http://opizo.com/api/v1/';

    private $options = array();
    private $api_key = "";
    private $debug_mode = false;
    private $error = array();
    private $debug = array();

    private function __construct()
    {
        $this->options = get_option('opizo');
        $this->api_key = $this->options['api-key'];
    }

    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function EnableDebug($enable = true)
    {
        $this->debug_mode = $enable;
        return self::$instance;
    }


    public function Links()
    {
        return $this->ApiRequest('links');
    }

    public function SearchLink($search, $type = SearchType::Url)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $search_str = "";
        if ($type == SearchType::Url)
            $search_str .= '?url=' . $search;
        else if ($type == SearchType::Hash)
            $search_str .= '?hash=' . $search;

        $response = $this->ApiRequest('search' . $search_str);
        $return = $response;
        /*
        $return  = false;

        if(isset($response['data']) && count($response['data']) > 0)
            $return = $response['data'];
        */
        return $return;
    }

    public function Shrink($url)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest('shrink', array("url" => $url), 'POST');
        $return = $response;

        return $return;
    }

    public function EditLink($link_id, $new_url)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/edit", array("new_url" => $new_url), 'POST');
        $return = $response;

        return $return;
    }

    public function SetLinkDirect($link_id)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/direct", array(), 'POST');
        $return = $response;

        return $return;
    }

    public function SetLinkRevenue($link_id)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/revenue", array(), 'POST');
        $return = $response;

        return $return;
    }

    public function SetLinkButtonText($link_id, $button_text = ButtonText::_Default)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/set_button", array('text_id' => $button_text), 'POST');
        $return = $response;

        return $return;
    }

    public function DeleteLink($link_id)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/delete", array(), 'DELETE');
        $return = $response;

        return $return;
    }

    public function GetLinkInfo($link_id)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("links/$link_id/info");
        $return = $response;

        return $return;
    }

    public function GetAccountInfo()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("account");
        $return = $response;

        return $return;
    }

    public function GetMessageInbox($unread = false)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }
        if($unread)
            $response = $this->ApiRequest("messages/received?status=unread");
        else
            $response = $this->ApiRequest("messages/received");

        $return = $response;

        return $return;
    }

    public function GetMessageOutbox()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("messages/sent");
        $return = $response;

        return $return;
    }

    public function ReadMessage($message_id)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("messages/received/$message_id");
        $return = $response;

        return $return;
    }

    public function SendMessage($title, $text)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("messages", array("title" => $title, "text" => $text), 'POST');
        $return = $response;

        return $return;
    }

    public function GetStaticsSummery()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("statics/summery");
        $return = $response;

        return $return;
    }

    public function GetStaticsChart()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }
        if(is_rtl())
            $response = $this->ApiRequest("statics/chart/week/?date_type=shamsi&date_format=m/d");
        else
            $response = $this->ApiRequest("statics/chart/week/?date_format=m/d");
        $return = $response;

        return $return;
    }

    public function GetStaticsFinance()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("statics/finance");
        $return = $response;

        return $return;
    }

    public function GetStaticsRefer()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("statics/refer");
        $return = $response;

        return $return;
    }

    public function GetBlackListURLs()
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("blacklist/url");
        $return = $response;

        return $return;
    }

    public function ReplayMessage($message_id, $text)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("messages/received/$message_id", array('text' => $text), 'POST');
        $return = $response;

        return $return;
    }

    public function SetAccountInfo($account_info)
    {
        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $response = $this->ApiRequest("account", $account_info, 'POST');
        $return = $response;

        return $return;
    }

    private function ApiRequest($resource, $data = array(), $method = 'GET')
    {
        $api_endpoint = $this->api_endpoint;
        $api_endpoint .= $resource;

        if ($this->debug_mode)
        {
            $this->debug[] = __FUNCTION__;
            $this->debug[] = func_get_args();
        }

        $return = $this->request($api_endpoint, $data, $method);
        if ($this->debug_mode)
        {
            $this->debug[] = $return;
        }

        if ($this->debug_mode)
        {
            $this->printDebug();
            exit;
        }

        return $return;
    }

    private function request($url, $data, $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => array("X-API-KEY: $this->api_key")));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);


        if ($err)
        {
            $this->error[] = $err;
            $return = false;
        }
        else
        {
            $return = json_decode($response, true);
        }

        return $return;
    }

    private function printDebug($data = null)
    {
        if (is_null($data))
            $data = $this->debug;

        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }
}

abstract class Enum
{
    protected $value;

    /**
     * Return string representation of this enum
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Tries to set the value  of this enum
     *
     * @param string $value
     * @throws Exception If value is not part of this enum
     */
    public function setValue($value)
    {
        if ($this->isValidEnumValue($value))
            $this->value = $value;
        else
            throw new Exception("Invalid type specified!");
    }

    /**
     * Validates if the type given is part of this enum class
     *
     * @param string $checkValue
     * @return bool
     */
    public function isValidEnumValue($checkValue)
    {
        $reflector = new ReflectionClass(get_class($this));
        foreach ($reflector->getConstants() as $validValue)
        {
            if ($validValue == $checkValue)
                return true;
        }
        return false;
    }

    /**
     * @param string $value Value for this display type
     */
    function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * With a magic getter you can get the value from this enum using
     * any variable name as in:
     *
     * <code>
     *   $myEnum = new MyEnum(MyEnum::start);
     *   echo $myEnum->v;
     * </code>
     *
     * @param string $property
     * @return string
     */
    function __get($property)
    {
        return $this->value;
    }

    /**
     * With a magic setter you can set the enum value using any variable
     * name as in:
     *
     * <code>
     *   $myEnum = new MyEnum(MyEnum::Start);
     *   $myEnum->v = MyEnum::End;
     * </code>
     *
     * @param string $property
     * @param string $value
     * @throws Exception Throws exception if an invalid type is used
     */
    function __set($property, $value)
    {
        $this->setValue($value);
    }

    /**
     * If the enum is requested as a string then this function will be automatically
     * called and the value of this enum will be returned as a string.
     *
     * @return string
     */
    function __toString()
    {
        return (string)$this->value;
    }
}

class SearchType extends Enum
{
    const Url = 'URL';
    const Hash = 'HASH';
}

class ButtonText extends Enum
{
    const _Default = null;
    const SkipAd = 0;
    const ViewDownloadLink = 1;
    const ViewPage = 2;
    const ViewTopic = 3;
    const ViewPost = 4;
    const ViewFileDownloadLink = 5;
    const ViewMusicDownloadLink = 6;
    const ViewMovieDownloadLink = 7;
    const ViewChannelLink = 8;
}
