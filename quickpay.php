<?php

/**
 * QuickPay XML-API library
 * 
 * This class serves as an working example on how to communicate
 * with the QuickPay payment gateway
 *
 */
class QuickPay {

    const QUICKPAY_VERSION = 4;
    public $QUICKPAY_SECRET = '';

    /**
     * Constructor
     *
     * @param array $message
     */
    public function __construct(stdClass $message) {
        foreach ($message as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public function loadMessage(stdClass $message) {
        foreach ($message as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Commits the message to QuickPay
     *
     * @return string
     */
    public function commit() {
        $msg = $this->buildMessage();
        $response = $this->transmit($msg);
        // reset variables
        foreach ($this as $k => $v) {
            unset($this->$k);
        }

        return $this->responseToArray($response);
    }

    /**
     * Builds a QuickPay message based on class variables set
     *
     * @return array
     */
    private function buildMessage() {
        // 
        $message = array();
        $md5fields = array(
            'msgtype' => null,
            'merchant' => null,
            'ordernumber' => null,
            'amount' => null,
            'currency' => null,
            'autocapture' => null,
            'cardnumber' => null,
            'expirationdate' => null,
            'cvd' => null,
            'cardtypelock' => null,
            'transaction' => null,
            'description' => null,
            'testmode' => null,
        
        );

        foreach ($this as $k => $v) {
            $message[$k] = $v;
            if (array_key_exists($k, $md5fields)) {
                $md5fields[$k] = $v;
            }
        }

        $md5str = self::QUICKPAY_VERSION . implode('', $md5fields) . $this->QUICKPAY_SECRET;
        $message['protocol'] = self::QUICKPAY_VERSION;
        $message['md5check'] = md5($md5str);
        
        return $message;
    }

    /**
     * Transmits the message to QuickPay
     *
     * @param array $message
     * @return unknown
     */
    private function transmit(Array $message) {
        // Create a HTTP POST request with QuickPay message as data
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST',
                    'content' => http_build_query($message, false, '&'),
                ),
            )
        );
        
        if (!$fp = @fopen('https://secure.quickpay.dk/api', 'r', false, $context)) {
            throw new Exception('Could not connect to gateway');
        }
        
        if (($response = @stream_get_contents($fp)) === false) {
            throw new Exception('Could not read data from gateway');
        }
        
        return $response;
    }

    /**
     * Converts QuickPay XML response to an array
     *
     * @param string $response
     * @return array
     */
    private function responseToArray($response) {
        // Load XML in response into DOM
        $result = array();
        $dom = new DOMDocument;
        $dom->loadXML($response);
        // Find elements en response and put them in an associative array
        $xpath = new DOMXPath($dom);
        $elements = $xpath->query('/response/*');
        foreach ($elements as $cn) {
            // If the element has (real) children - this is the case for status->history and chstatus->entry 
            if ($cn->childNodes->length > 1) {
                foreach ($cn->childNodes as $hn) {
                        $result[$cn->nodeName][intval($i)][$hn->nodeName] = $hn->nodeValue;
                }
                $i++;
            } else {
                $result[$cn->nodeName] = $cn->nodeValue;
            }    
        }
        
        return $result;
    }
}

?>
