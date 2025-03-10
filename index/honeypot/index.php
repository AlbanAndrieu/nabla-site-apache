<?php

namespace Hp;

//  PROJECT HONEY POT ADDRESS DISTRIBUTION SCRIPT
//  For more information visit: http://www.projecthoneypot.org/
//  Copyright (C) 2004-2025, Unspam Technologies, Inc.
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
//  02111-1307  USA
//
//  If you choose to modify or redistribute the software, you must
//  completely disconnect it from the Project Honey Pot Service, as
//  specified under the Terms of Service Use. These terms are available
//  here:
//
//  http://www.projecthoneypot.org/terms_of_service_use.php
//
//  The required modification to disconnect the software from the
//  Project Honey Pot Service is explained in the comments below. To find the
//  instructions, search for:  *** DISCONNECT INSTRUCTIONS ***
//
//  Generated On: Tue, 25 Feb 2025 09:35:35 -0500
//  For Domain: bababou.albandrieu.com
//
//

//  *** DISCONNECT INSTRUCTIONS ***
//
//  You are free to modify or redistribute this software. However, if
//  you do so you must disconnect it from the Project Honey Pot Service.
//  To do this, you must delete the lines of code below located between the
//  *** START CUT HERE *** and *** FINISH CUT HERE *** comments. Under the
//  Terms of Service Use that you agreed to before downloading this software,
//  you may not recreate the deleted lines or modify this software to access
//  or otherwise connect to any Project Honey Pot server.
//
//  *** START CUT HERE ***

define('__REQUEST_HOST', 'hpr6.projecthoneypot.org');
define('__REQUEST_PORT', '80');
define('__REQUEST_SCRIPT', '/cgi/serve.php');

//  *** FINISH CUT HERE ***

interface Response
{
    public function getBody();
    public function getLines(): array;
}

class TextResponse implements Response
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getBody()
    {
        return $this->content;
    }

    public function getLines(): array
    {
        return explode("\n", $this->content);
    }
}

interface HttpClient
{
    public function request(string $method, string $url, array $headers = [], array $data = []): Response;
}

class ScriptClient implements HttpClient
{
    private $proxy;
    private $credentials;

    public function __construct(string $settings)
    {
        $this->readSettings($settings);
    }

    private function getAuthorityComponent(string $authority = null, string $tag = null)
    {
        if(is_null($authority)){
            return null;
        }
        if(!is_null($tag)){
            $authority .= ":$tag";
        }
        return $authority;
    }

    private function readSettings(string $file)
    {
        if(!is_file($file) || !is_readable($file)){
            return;
        }

        $stmts = file($file);

        $settings = array_reduce($stmts, function($c, $stmt){
            list($key, $val) = \array_pad(array_map('trim', explode(':', $stmt)), 2, null);
            $c[$key] = $val;
            return $c;
        }, []);

        $this->proxy       = $this->getAuthorityComponent($settings['proxy_host'], $settings['proxy_port']);
        $this->credentials = $this->getAuthorityComponent($settings['proxy_user'], $settings['proxy_pass']);
    }

    public function request(string $method, string $uri, array $headers = [], array $data = []): Response
    {
        $options = [
            'http' => [
                'method' => strtoupper($method),
                'header' => $headers + [$this->credentials ? 'Proxy-Authorization: Basic ' . base64_encode($this->credentials) : null],
                'proxy' => $this->proxy,
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $body = file_get_contents($uri, false, $context);

        if($body === false){
            trigger_error(
                "Unable to contact the Server. Are outbound connections disabled? " .
                "(If a proxy is required for outbound traffic, you may configure " .
                "the honey pot to use a proxy. For instructions, visit " .
                "http://www.projecthoneypot.org/settings_help.php)",
                E_USER_ERROR
            );
        }

        return new TextResponse($body);
    }
}

trait AliasingTrait
{
    private $aliases = [];

    public function searchAliases($search, array $aliases, array $collector = [], $parent = null): array
    {
        foreach($aliases as $alias => $value){
            if(is_array($value)){
                return $this->searchAliases($search, $value, $collector, $alias);
            }
            if($search === $value){
                $collector[] = $parent ?? $alias;
            }
        }

        return $collector;
    }

    public function getAliases($search): array
    {
        $aliases = $this->searchAliases($search, $this->aliases);
    
        return !empty($aliases) ? $aliases : [$search];
    }

    public function aliasMatch($alias, $key)
    {
        return $key === $alias;
    }

    public function setAlias($key, $alias)
    {
        $this->aliases[$alias] = $key;
    }

    public function setAliases(array $array)
    {
        array_walk($array, function($v, $k){
            $this->aliases[$k] = $v;
        });
    }
}

abstract class Data
{
    protected $key;
    protected $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function key()
    {
        return $this->key;
    }

    public function value()
    {
        return $this->value;
    }
}

class DataCollection
{
    use AliasingTrait;

    private $data;

    public function __construct(Data ...$data)
    {
        $this->data = $data;
    }

    public function set(Data ...$data)
    {
        array_map(function(Data $data){
            $index = $this->getIndexByKey($data->key());
            if(is_null($index)){
                $this->data[] = $data;
            } else {
                $this->data[$index] = $data;
            }
        }, $data);
    }

    public function getByKey($key)
    {
        $key = $this->getIndexByKey($key);
        return !is_null($key) ? $this->data[$key] : null;
    }

    public function getValueByKey($key)
    {
        $data = $this->getByKey($key);
        return !is_null($data) ? $data->value() : null;
    }

    private function getIndexByKey($key)
    {
        $result = [];
        array_walk($this->data, function(Data $data, $index) use ($key, &$result){
            if($data->key() == $key){
                $result[] = $index;
            }
        });

        return !empty($result) ? reset($result) : null;
    }
}

interface Transcriber
{
    public function transcribe(array $data): DataCollection;
    public function canTranscribe($value): bool;
}

class StringData extends Data
{
    public function __construct($key, string $value)
    {
        parent::__construct($key, $value);
    }
}

# function gzdecode($data) 
# { 
#    return gzinflate(substr($data,10,-8)); 
# } 

class CompressedData extends Data
{
    public function __construct($key, string $value)
    {
        parent::__construct($key, $value);
    }

    public function value()
    {
        $url_decoded = base64_decode(str_replace(['-','_'],['+','/'],$this->value));
        if(substr(bin2hex($url_decoded), 0, 6) === '1f8b08'){
            return gzdecode($url_decoded);
        } else {
            return $this->value;
        }
    }
}

class FlagData extends Data
{
    private $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function value()
    {
        return $this->value ? ($this->data ?? null) : null;
    }
}

class CallbackData extends Data
{
    private $arguments = [];

    public function __construct($key, callable $value)
    {
        parent::__construct($key, $value);
    }

    public function setArgument($pos, $param)
    {
        $this->arguments[$pos] = $param;
    }

    public function value()
    {
        ksort($this->arguments);
        return \call_user_func_array($this->value, $this->arguments);
    }
}

class DataFactory
{
    private $data;
    private $callbacks;

    private function setData(array $data, string $class, DataCollection $dc = null)
    {
        $dc = $dc ?? new DataCollection;
        array_walk($data, function($value, $key) use($dc, $class){
            $dc->set(new $class($key, $value));
        });
        return $dc;
    }

    public function setStaticData(array $data)
    {
        $this->data = $this->setData($data, StringData::class, $this->data);
    }

    public function setCompressedData(array $data)
    {
        $this->data = $this->setData($data, CompressedData::class, $this->data);
    }

    public function setCallbackData(array $data)
    {
        $this->callbacks = $this->setData($data, CallbackData::class, $this->callbacks);
    }

    public function fromSourceKey($sourceKey, $key, $value)
    {
        $keys = $this->data->getAliases($key);
        $key = reset($keys);
        $data = $this->data->getValueByKey($key);

        switch($sourceKey){
            case 'directives':
                $flag = new FlagData($key, $value);
                if(!is_null($data)){
                    $flag->setData($data);
                }
                return $flag;
            case 'email':
            case 'emailmethod':
                $callback = $this->callbacks->getByKey($key);
                if(!is_null($callback)){
                    $pos = array_search($sourceKey, ['email', 'emailmethod']);
                    $callback->setArgument($pos, $value);
                    $this->callbacks->set($callback);
                    return $callback;
                }
            default:
                return new StringData($key, $value);
        }
    }
}

class DataTranscriber implements Transcriber
{
    private $template;
    private $data;
    private $factory;

    private $transcribingMode = false;

    public function __construct(DataCollection $data, DataFactory $factory)
    {
        $this->data = $data;
        $this->factory = $factory;
    }

    public function canTranscribe($value): bool
    {
        if($value == '<BEGIN>'){
            $this->transcribingMode = true;
            return false;
        }

        if($value == '<END>'){
            $this->transcribingMode = false;
        }

        return $this->transcribingMode;
    }

    public function transcribe(array $body): DataCollection
    {
        $data = $this->collectData($this->data, $body);

        return $data;
    }

    public function collectData(DataCollection $collector, array $array, $parents = []): DataCollection
    {
        foreach($array as $key => $value){
            if($this->canTranscribe($value)){
                $value = $this->parse($key, $value, $parents);
                $parents[] = $key;
                if(is_array($value)){
                    $this->collectData($collector, $value, $parents);
                } else {
                    $data = $this->factory->fromSourceKey($parents[1], $key, $value);
                    if(!is_null($data->value())){
                        $collector->set($data);
                    }
                }
                array_pop($parents);
            }
        }
        return $collector;
    }

    public function parse($key, $value, $parents = [])
    {
        if(is_string($value)){
            if(key($parents) !== NULL){
                $keys = $this->data->getAliases($key);
                if(count($keys) > 1 || $keys[0] !== $key){
                    return \array_fill_keys($keys, $value);
                }
            }

            end($parents);
            if(key($parents) === NULL && false !== strpos($value, '=')){
                list($key, $value) = explode('=', $value, 2);
                return [$key => urldecode($value)];
            }

            if($key === 'directives'){
                return explode(',', $value);
            }

        }

        return $value;
    }
}

interface Template
{
    public function render(DataCollection $data): string;
}

class ArrayTemplate implements Template
{
    public $template;

    public function __construct(array $template = [])
    {
        $this->template = $template;
    }

    public function render(DataCollection $data): string
    {
        $output = array_reduce($this->template, function($output, $key) use($data){
            $output[] = $data->getValueByKey($key) ?? null;
            return $output;
        }, []);
        ksort($output);
        return implode("\n", array_filter($output));
    }
}

class Script
{
    private $client;
    private $transcriber;
    private $template;
    private $templateData;
    private $factory;

    public function __construct(HttpClient $client, Transcriber $transcriber, Template $template, DataCollection $templateData, DataFactory $factory)
    {
        $this->client = $client;
        $this->transcriber = $transcriber;
        $this->template = $template;
        $this->templateData = $templateData;
        $this->factory = $factory;
    }

    public static function run(string $host, int $port, string $script, string $settings = '')
    {
        $client = new ScriptClient($settings);

        $templateData = new DataCollection;
        $templateData->setAliases([
            'doctype'   => 0,
            'head1'     => 1,
            'robots'    => 8,
            'nocollect' => 9,
            'head2'     => 1,
            'top'       => 2,
            'legal'     => 3,
            'style'     => 5,
            'vanity'    => 6,
            'bottom'    => 7,
            'emailCallback' => ['email','emailmethod'],
        ]);

        $factory = new DataFactory;
        $factory->setStaticData([
            'doctype' => '<!DOCTYPE html>',
            'head1'   => '<html><head>',
            'head2'   => '<title>season palatial bababou.albandrieu.com understanding attendance</title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVSsvPyckv18nLz8xLSa0A0olFyRmZZalKdgCebds4NwAAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA71abXPbNhL-fr8C59y0yYzj2KntxKXrGdWRY90ktk9S0slHiIRIxBTAAKBV9dffvgAU5cRWru3cByc2RRKL3WeffXah0yBntRK5qutGFoU25S87-ztiZl2hHP2KH_lG5vGjs9Pgzv5xGoqz00LfCR9WtfplZ25NeD6XC12vfha5bZ1WblcsrLH4qMp2zn4wM99kgv-Lf5zOzk7hc5Pektvaup-fXFxcwAPy9AV-dnb6Yrb5MDzm4hX8bVopsVQzrwP-FaxY2fbht9r4Vt_O6DVPDg4OslAp_OUwE0G5BV7_DV-I144zJVZ8F153ShYCbl-QERI-2P8pUyZ4Yed0KVR47SBb7Ikfnrw-zMA4vGyseR6cNPimo2zeLRFg4cNjevV6V_-Dk26_00mTRuWwn5NXWS2cwitXYNFlu4D3Jj8cZY0uoiP8ruj88zJztnRywfeIgnbeBruQAfd6lOUy2T9cSF0LwJHDv0ZBaA_L5ZZfhatbE3f84JZC3BK47ySDUMKjlfRCClz_dUY-f53RdR-sk6XaFTLZCp_XrcJghPW2AsfPAYIhdp0tHDyn1AICKO6F4FGv5w96_V7k-iEYxkVPTrJKsF-lCyvyZim18af_fP5czABflayDmDl7q4zIV3mtc_H8-VmItj-l-5bWQILCJad8ZetCyLbQ4A7hlXMyKPGlxVDAczv_hk9yLevkkGMEJYdZldqTo3ybV6KWS8-4NkW0ULm5pVja-cPuaLqIHezDI2BXWgw2bFYPP6jigzKPSLK46j4jVrk7nXMy7YlPG8bLmb1T9yP2_UmjvjNpPtkWuMx4ZfCvBW1nJWTTKEk-kbPWJwOBhfDSRw05HeHmRZcXH0eT0fR6PBED_OtmMCZu-CSux8gSP_GGP_w6Gf7nw_BqSk-9AHaFf4Fn_zTZ_knm-Nol-KO2Z-zc2YVYVhpv_6xyDtfDj83jY0DZMbKERFFCcAnj3iJsAaFCmztb36lCtKbWC2DNAt6o5ALDk9et1wAHgLozmkLVCplH6DT8aqaDXDo1b5lA6tUe87WCt4NzdY8XIEVz5YG6dElbd1gjt1eqJZegTVx-DyDDV7GA2yc3w_MRXknv87wVp_OgAZUd3KzzexQmG4C2YTPqd7La69KAowKxbx33y59E2EZGj1nXUSawotF_4KOVXG9E5reG7jzJan682IKLVGuNDaJWvBiZbYoXwFRF5J5KeYU1I1ISXoKyAeWRn4BoMycRJoAngwS6zmub3wpfSYfZCLYCJ7QG9QsCgeiLN_hyP2uAI9eBeSQO5UM58edY5V4GRTeWUHF8V4fQyu0etGuFwgjndw0ICTtQUA94GYWeGBQLvgn8gmzFeGeCjQsynVEMBRQ_7ci3wel20UD1QReqnsmm2M4jEJbQe2bG5tq2rALzIJMpxxTW5LpH1ZfhF_Y2HfVtOrrnUyiKFuoll369rh4xALQYgnb_JFPLSrn1_vmOUaEMp5WeazK_jDBUgtGeuAN_3o6Hw-1lcHo5mEZRdXwCNrxC5x-_zkZXbwGWx0fZ1ae1Ab-Oh4PzS3F9QRGoAexQvVtHdPZ_qQMPyuw15qlYDcd9Lsqr7X4AHS6IUPOcsCnmtq4t7VPOPGouZ1cANtjuTBdCL2SpjQyRy5eM4RKonAgZ-XhNF1Gau4XvR_uYAA4QWIkrLsIQ3MOj7BJvKlpZP_XPoEDRW0Cyg6g1jNnKtiivlFjjo0cYg3cbxPxudE6RvZrQf323rGEHS2_nmaRpdWS6r3W5ckDVh5D2zs7YdUBmpUEGREnYX5McDV7aFa5vfwVc2hEpZ2JI9RY9qajfKSMBtECqs1p6rLBNpWvgV8ATCFDkUA8RdIu2lsQPQrKvgXpLtzaElNxhlhQ21hXAz1E2Ef-iBgq7EGKjg1cZspeg8uS98tQXwM2vfspiTlJsfvQxKizANNPZw84tknPpvfUKTLeVnm1Ri4Obm3cb1VYGEfWr8K3ecDS0JcYkfROFKy0G1FmLGVsrSi5k0PURpAmCyFZEelSSVwIUQ6UfAUqSSOvlIc2ZoEIs3ku9rtrsIKiQIgUBbVv0gmOFqlUeNhByn1nPxtCqHL_Krsdv-g4psbF2YmR6qIPwBQRYw_CplHUrTCvMNeFNO5utEpE57AVbo7-0dG9uFw1IOWABCY_MWyAB8BV3OlQtBEPEP6IvfBKQYEKy8nIw_jicTIFr11a-H5LIngzeDp9O6MatRXx6Sbk9GYopEN9f5mFw6nsW-n0i4WLwZuPa-fVV3-mQdKTDOJKe4wBlFHo9bVNPBGBj7nr9Khb4WY8WsZmjJ0gZI7GQamQnwwfB2donDKEKpAmKoAb79yZWQ8j9pdNfo4arw8sMXD4eEWqmG1kE4vTHCFRoY1kfPHmdsobFq6D3Am_lTi5rRdhUsiCYFA5VfqPyttYS213rDEEK7o51nRvVDdugfGLfpnlHa06XvCxPKFS36YNModQNQhFnHO1DvwkemJML5iJqL1At8BzJ16gYrIE-dys6efpBC8bX-O0JT9uhThI12t56c2fnA3DzcfZueA9Kr46zwW_9azEAu1BAA7dTIJxzJe5UqQKk54J1RmOX0OS3tZjXreKygqxM-xQs94GkuEc4ZOEF_ZnDzmJGw4vzWO5CWgfaQHhP09I0EfipUGqFDRskRk6lPRHpIYg-kqI0QJupsCSLAcqDgmOrTRyqaUMQHzyevUl5aLScaJoNgsZAEtXUIJ9BYWnoPsAO4HDaDta3GBp3twEkhve9tAWttul6wPOIFiptXcy1yaGwitougeADCmCRS0AK-EUpa2ZWugJXn_AzX1qlAKp_KHDAXIcQO5jpBgs4o6IWvXE2SmclnqJLdugGWAriGSdI5ta1TchXwlEPKKnMQnIZjYOhCvqlMIO8usWlnKrpc1_pBtfNq77w5wRC6D6C2cSZ82Turng7IM14ORyPrvDKMzG9FtPLIVd3vPJ-sr0dvr4Qk-H4499Cv29o5Sn9e331UFC7uG9XFxvTw8TE-5mnUZDneg-Ka0_8uhIMqqApPp8hyMoRiYHbKYiEwjh4FWFN6eTM75CRUU2xtPY0NoFAFxYKvTbIhZImJpjwBi_A0lUaUm1vPhPlARJo4qgETahgF48olzQibUCj30-pa-qJHgFAyuOL6_FGkK6u-0Ey4GXUU9xe4ByoULuC5GUbKwrI17tI3hSjWglI_ZJ0EnfgXcnoBgawze3hB03ZcD30j9F5ciF24o3jxgkUNw3JE2YOe8cPqA2pWmDGAYn4NtWo12noQi3_LsHLpyluzsEsGYJY9l6-_KqJuSc0IAaH2YfxaLIh8sA7aeSfM5tYLMmibaBuuCL2HWrDWchGIN5i910Cz222QjSyreVS_NAdo8RDErEz4HcsItHHFxuaYdD6rQfBip2HWoq8ksRRfO8EBw7cM3yynAHkugXbrctaybJVzPhz5ZSJ1Yc0gBETti1OLYKOZUws-QaoeRQvOkyIMshSVbmP5k2MfphsVuYBgX3TyVycogzBKziGCDiBINc9Mm1p1jPTeCoFkrwLHLcrERTd8BkMOMh2xQTJ7yQbR2n8Zng1HV2MhmN6PLa0e3-da_t1Ebq-4deShP96utNh4zdF3VmIWcCl3wtIGm4VZAuYbz0iENXwQoFkbWytQc-wgJWFzgO2Hh70YVhLDEqSGE4sdk9j7NdW7ExxhgAhXl-K53LC91hZ4ADOcasVHxATlTIYhGzsAk138tQDyNXzyw_vGQYbyPjIxbA_PiD046ADvcLtPsozIWvQ7TKnkSnmYIXHkN2QjpUBcgxOWtlGID-AhDT81_ZamycVl6Zx_U0oatJ0HJbYO5B0hZhH3yR1Gz2R6pairgV5Nl2ZxQE2bEBDo0w8EKdeMwcMAhbC_j63JqeGOW8dsEolSKbMpae4Ip_7eFhkeDhf8CDiKOME2M7dceZyL0qj8-mIe69NtOLqVFMcSrnk74NswZWQM5aaBBTYoWOmnWfgoe4vkmJ9WuypU-s91AOsKNbz7NWnoSRjCY-_uGcGzjDWAdqhMsw-QyMPQaETqi9Ju0dSSPqXQIxijzQp2ZPaEDCpBCnad8OatH798K3M9euKE4NfUGRhr9RrvczEMibOKMIIpGtXLB49DUql8oYWsqbreXb5IH08uJpccC8OPTJ0-NMP02Fn_F-Uh3F7GsO2HUDapzPz_TRwRCpPLfnDL6iSHDAo143pSe0Jc52z9tYLGp-AYAf0x5AeZnfYPtMtqgG17zQe_2JK0uAd2g2LlVLOEUgL7fMWGROPf59RouFkimCsIjRYuwgmW3n7mIpJOjOJEbKGh7Kce_HUJlV1u4wxd4y4XpZNOhkOlWjyjdqAJuJ0gndqNE4csYPVdzqs4tyxaYg8ar2I3N6dcq2YqLfL0nytRdgAOrvsKlLf6hoHZhiQZn131fIw0oC053P72Eax2riDu--UKRX21XGgtVlc8vUlnIRpfJo3UMShXZRTcVBScSaUMpnoeDxua_42gHSpqUUzIibtfNP76chkhiNNlne3xtZkIqudVNkccyTzOFxB9lqrPW4CcCvE_dS9q1Qctmc4Pvw5TjG5oho6xollgrjUI7Uy_XKjRRiw5lsbYsP2xMDgMJspVS5YDCApqyV23GAIZL7H6XZ-y2mFw1J8QesbpNKGO2tosBkcOSm0_kqonQSDVwxYhIrBFS25si0oRnhRXdNBpPB5ZWvpUl8dD-zOz4c3fy9pQbpTPcbfTdc8xtk8DWjcs-TaPXEDFOHXAzGuQHmlc_yuyZcW0Ns2tc6RUbAO6dT2qXX0fcryDl3z9P77P6uYlnyEsYyzyRSzEz6cf4VFAq_1v3N1L76ovRtG5XHGB9nxTutYp5lAX6HpyjPvf5cLXrKv3AVFVLzggxSPeQrIA4ZRcFXOA7aSre9OoPqHGr0Jc_-nIRC5OPeyrlQhpvwqnnPht3G4MkejT056A8kotuJ3FbbTFvs8dkZNAKKlYyDgRDIvhQlZiAm-oC8L0P13FkkGOmBIt6r1WvoF8gado7dmLnWoou3m8SqWehBgOGqvaOVOROOQv1DuW97CdGIDaaDEmc8Ex18YorZ3r_8IJtvLbDR-w5EFHTC4On_kDFZH21LX9a1Ue0FfYHxBX3yEX-Dj_wITAdA0BSkAAA',
            'style'     => 'H4sIAAAAAAAAAyXMSwqAIBAA0KsEbbPPVqWl9xh1BEFmYpygiO7eoneA57veDXeYVc74JG4sdgwhuMKkNnLLw7Ye1wBSoU0dqJuOUotTvNRkTCyglckSE7rXL__3Aa0BC9pXAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22S207DMAyGX8XKbtk6Bkxa1lWIaQghwSYOF1ymTdYGQhw53srenrSMCw6KLNlR_P2_k-SsSmegMs7FoCrr64UYi64MSutjWSJpQ10W-eDMQpSqeqsJd17LwWw2m7dWcyMnZ-PwMRdFzpRCw145W_uFYAzfjUeohNPwAZMUFynOU9eXxJBs3bCM6KzujwyWy2VHTN48HBlb9CxLdBo6PVBklTuJysdhNGS38wodkhxMp9N5Upadp4DRskUvyTjFdm8S8zLPOmqRZ6z_2IVj7syWBfwyf5ZUx2mdf02roCGzXYiGOcgsa9t2FAhfTcUNenMIyCOkOhNQORVj4tOuFMXd6u5q9QDra9g8rG9Xyye4Wd-vXmCzfsozVeQl_Uve-WT6fVThu_iBe0y7cKNobyIbgg0hJwNpZLg33CK9dcBka2-10VAe4LkH9VL9BWTdo2X9byg-Ad-PsBAVAgAA',
        ]);
        $factory->setCallbackData([
            'emailCallback' => function($email, $style = null){
                $value = $email;
                $display = 'style="display:' . ['none',' none'][random_int(0,1)] . '"';
                $style = $style ?? random_int(0,5);
                $props[] = "href=\"mailto:$email\"";
        
                $wrap = function($value, $style) use($display){
                    switch($style){
                        case 2: return "<!-- $value -->";
                        case 4: return "<span $display>$value</span>";
                        case 5:
                            $id = '95log';
                            return "<div id=\"$id\">$value</div>\n<script>document.getElementById('$id').innerHTML = '';</script>";
                        default: return $value;
                    }
                };
        
                switch($style){
                    case 0: $value = ''; break;
                    case 3: $value = $wrap($email, 2); break;
                    case 1: $props[] = $display; break;
                }
        
                $props = implode(' ', $props);
                $link = "<a $props>$value</a>";
        
                return $wrap($link, $style);
            }
        ]);

        $transcriber = new DataTranscriber($templateData, $factory);

        $template = new ArrayTemplate([
            'doctype',
            'injDocType',
            'head1',
            'injHead1HTMLMsg',
            'robots',
            'injRobotHTMLMsg',
            'nocollect',
            'injNoCollectHTMLMsg',
            'head2',
            'injHead2HTMLMsg',
            'top',
            'injTopHTMLMsg',
            'actMsg',
            'errMsg',
            'customMsg',
            'legal',
            'injLegalHTMLMsg',
            'altLegalMsg',
            'emailCallback',
            'injEmailHTMLMsg',
            'style',
            'injStyleHTMLMsg',
            'vanity',
            'injVanityHTMLMsg',
            'altVanityMsg',
            'bottom',
            'injBottomHTMLMsg',
        ]);

        $hp = new Script($client, $transcriber, $template, $templateData, $factory);
        $hp->handle($host, $port, $script);
    }

    public function handle($host, $port, $script)
    {
        $data = [
            'tag1' => 'bd83ef9273ea15a511afd49a21b91084',
            'tag2' => 'fdc0d7b1f67254bd4d48feaaa3064766',
            'tag3' => '3649d4e9bcfd3422fb4f9d22ae0a2a91',
            'tag4' => md5_file(__FILE__),
            'version' => "php-".phpversion(),
            'ip'      => $_SERVER['REMOTE_ADDR'],
            'svrn'    => $_SERVER['SERVER_NAME'],
            'svp'     => $_SERVER['SERVER_PORT'],
            'sn'      => $_SERVER['SCRIPT_NAME']     ?? '',
            'svip'    => $_SERVER['SERVER_ADDR']     ?? '',
            'rquri'   => $_SERVER['REQUEST_URI']     ?? '',
            'phpself' => $_SERVER['PHP_SELF']        ?? '',
            'ref'     => $_SERVER['HTTP_REFERER']    ?? '',
            'uagnt'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        $headers = [
            "User-Agent: PHPot {$data['tag2']}",
            "Content-Type: application/x-www-form-urlencoded",
            "Cache-Control: no-store, no-cache",
            "Accept: */*",
            "Pragma: no-cache",
        ];

        $subResponse = $this->client->request("POST", "http://$host:$port/$script", $headers, $data);
        $data = $this->transcriber->transcribe($subResponse->getLines());
        $response = new TextResponse($this->template->render($data));

        $this->serve($response);
    }

    public function serve(Response $response)
    {
        header("Cache-Control: no-store, no-cache");
        header("Pragma: no-cache");

        print $response->getBody();
    }
}

Script::run(__REQUEST_HOST, __REQUEST_PORT, __REQUEST_SCRIPT, __DIR__ . '/phpot_settings.php');

