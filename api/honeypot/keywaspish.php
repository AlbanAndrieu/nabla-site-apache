<?php

namespace Hp;

//  PROJECT HONEY POT ADDRESS DISTRIBUTION SCRIPT
//  For more information visit: http://www.projecthoneypot.org/
//  Copyright (C) 2004-2026, Unspam Technologies, Inc.
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
//  Generated On: Mon, 05 Jan 2026 15:03:43 -0500
//  For Domain: nabla.albandrieu.com
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

define('__REQUEST_HOST', 'hpr7.projecthoneypot.org');
define('__REQUEST_PORT', 80);
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
            'head2'   => '<title></title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVystPLErOyCxL1cnLz8xLSa1QsrPBpz4tPycnv1zJDgDzslacVQAAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA5Vaa3PbthL9fn8FrnMnTWYc5-k4vkwzozpKo04r50pKOv0IkpCEhiRYgJTi_vp7dheQ5IfkpjOuJBIAgd2zZ88u87bTeWVUYaoqtLqwzeLHo2dH_LvVZZl-586XxtPXd_9623n6X6lCd1WZH4_mrumezHVtq6v_qto1jhYy2dG7h00e2kzFj7f5u7c0VBWucljqwQf-7-idfvuUrr97-zS_MeX-j83aPk6lb7OHD54_e5XR_59nam3yYDuj5h4Xnj_ParVeWp6An68zp65crwL9OMvyP83DB-fnWac6p7oljXLd0njVyWK-DmrhVvFHYxsa8Ts9QBYzvJguCtNiCbrpjS5l9qvM1KrQNBBf6cqLrKePN1l1dUJj9cI0XVBufthO2JCyTWnf_vvJE6Wbbuld62rn26Ut1JMn71Z8OoyqT9QMY3XBZzIhKHm4XfDU0tSu8LpTofett8HQZJrauOZJ53UT5sYzOta22xy0M7zXrcXv9e9yn3_vmxjSxDvvfj14191-KJ4z_TS8GNGl64CZtqawuormMaHzVmBgXRNowBgW-djXusGQN68zCzM4H04Yd6G1CI1wjBgREOArufU0a0rzjUa03i28BnRKE-yiYd8_ywAxdnofcVjjkWd4JD9ZVxET9Desta0UotHDhwYAaQRbo04x7k6zu20wjzaIxyrcgr49y-zfAmAAc6l5D4IHWr7WXwF3fMOhmsKosNSlW6u_euOvVGlXODoeD6BUrjEnZI7zTOKKcP-1cesqno8WXmrZn9JqpSt-SgPz0vzeqMZ1SgLAhHiOGJanWedk14gJMics05RPnccWAp8dy0nIGsQLzmEAXy0PxjnYL3CRlQMvMMAGFfEroeaNqU0jtkcURZ-Yby3OfifA70Pr1_tobO_M21jdO9QcBH2xdwvX8T4kSiBGUq323ZWCNQQRJrpLqI_t-TLTtgnRTkQ9EeWR0rwSEnx09EsvNrQcSoOCgufoMT858d9hfvBmAecab0o1KGtZNvTFUlV6HQQDfs8SySy6bStr0t7h-VK1xs8FoL42JUMQz8DyzGwL42qDcC-Ubl0FTFvE-Mq6CkyMkwUQA2BEeBXr4bIVeDlhcDWPcDcRr9XdO-zSDpvyMAhCpyNEGzim6KxsvlG5d_1i2R3ODWwlpjTjV7YQr57wqT1xiqI0gSMiphs8yfqgAg67PeAfyF-FY4djZtPFVKJ07lZGcdjQrXOyxJ5E8M8_vkcS7B1rd8Zeh3k6C_kS-XypVwKqWsOybWu0x-Frs0aCF4vnfTw3Jc4oH0Yl-XwuqPpiU6aP9BTEJKCyFCHJJl9G4MaXCJOzlxkEydl5Npmqwc-T4fAwfcw-DmYcNJ8Gk9kf6nKipsPxwwev32QjfJxhR4PxH-zhzz9Nh__7PBzP1E941ItsOLjAAEDyEh_Cv2-fduU_02r309phRx1MQP-A2OydHp8NJ79Nr82GJU6zseQrDQR7HK21wLYKVV_nBkESCM3vr826uBwneNAfOAU67e6NtClQOQ_HCBJaDJvMjWwSUxVBZO6ijHNr6GWCXGnBfjRisau-WFMaTkOcHClngYsknkLKcjQ_MukWzgOQwVaEEAwZaGXPlgBLFniApQhtvlkyQd7X7RUZQsveHoXH0L1OFTyhhzTxaxCNVytnCzBuX3W2xgVolJw-MBNW8K7aJuYltCl0TNNheOf2WzB5vedJru_41EZ22jrP6R_LvniWUbINau1t8oz8iZd_Pbz8rxxaF8PxdHjN15PhdDYZXcz49q7XZYSTxECKRFQaa7kfAm-vvYKWXjovQlomVLDPfg2oeOuR_MVWDx-8QmaN1UbuOujBIt2Hh0Ug4dd2T3xd8AZ7HCsqGlj1nEMWtteMQ8g7nD-o1tHeMPNBxDGBR4EUqZsfZwUXLCsFxwzKBUGoZFaTq4dZoUulFh-AGdDo7cE6yjWfp-o_p89OFNFxLHu4MrJ5TxpBpUxKezz0zDJFZsnKINDoV3jgCIiMQg7LgQkKDr0fgjK7wpkdDPyuDLii5bqsRC5nnYJJf_XWdCrXgenDHNLVydTsmICkSoNfZN4tbZ4KwfJGcvz-jIehg0-ffh1dDEDvZ6-z4Q0exKXB77vX5HGi5XVr-BydIhkRetsds5iAubd7AkIakQamsouo62EQvpb38zkMUvS5nTtfq8DaDIftjKZxNJzOep7ZCAJoCymIlqzNAa67j7qIR80N1dLGN-CT_Ipm4hGoR-2KN7B2nkqRui9c0NBicJBnRjPqwkV_40jqkTAhlVMH-WIL6ZrLbGAWhUcnZ6AlcDYUustYuzFpsQ7dtZgEtVQevotqmWZZmhDzAbMXBxNJZ6lxUHXXqSyk0GxMsRMmTpnKFHImDxHKoa8C54bnb7Kb9HhYYm4xsQXdZHhxOXnPiuQajC4_3EAVmG00Hc12eXNxOFEiDwifKWZcBCM82qlPkQC7HeptJHMeLPZbVHzwKYx_xCyhULj1sW6lj5dZb-Ap5KpiKZyte6r9qoIEKsGD1bnMOUV5yKUfE5TwgJqng30cTL4gWYzGPx-rnwc4-6vs45Cl1Gi83fVvw-l0wMpr-Gj6WM0u1ezjUM7KCx2sxGasyACI82w6VCxn1OUHNeUrky_frdHei54cXY6nh_z4-XpO3CWiVPkicI6iGuGeTyTTpHtZmohf61TDI6lwlHYo_H-6uq8QYeAiG9lGykiY7BV82GwKwtOUi4N6tFtTxig6mqFMi4927DNKt_uiPBWRONJh1olNAMXZDqTYC92VNlBBWKo5zFPZFQk5Ko5xmWkRomAPcNOJXVRWXFd2pm6prUpghkqlTgoW8SrGBCqONQc16TAVd2BCYRqqNlTT-66HtfsFqlkQSKsxo4MoqW2gzixxumgoqqrVbCs_WEimhDo12zYnUiSVwlggJOZ-ns2V3LylvlCtjG-TxYfLybXfYxn45OPn3wbjO3hkl0QaApTj1igpYQYeXy-qXqry4qqxBRLxQrdkfL1YULQip9T2G8f18WHPM5_3e-rjFI_a87NW2ktWy3VVX8H0c9sQKJYOzoLLKk0RAjRwuhGUpgqPU9xxJHXtV4byYhDRh91qBjh8j-8LaekJT4HhNftQOsq0sheNt00qz8hP3a47qBvSesEWCtaK5rUeUkUjGdqm27gYVBc4RDF6Bd23p82Q0DrfPlSUKNj0YPrcaqlIEOxF3qIYhg61bSSlnrfuVLrNaTq2MEOfU1h0VlcV2yv2AVmiJONKy4cMDECw2aFUCid00ti_paSAxCypjOeYM9APFDnYDDsPShZWEXxVLrWXsEoslzYq_-5EOxhfryN_AbBPs8loKgV5LDIux7fJFt6HIwpj6BWK9G54F3McWgcoDV35niwB4sHOAm26ihJMmm8-9hqofTn3ruagmd2qHehqJYVF7ISq2EHVnVSZ6kjaaZwsPTBDO_pqy-3hp9RzOnp8WO3OWVUbMfra6K9Kf0NmXZJow8aphZbDFY7kXKdJ19EDmsNBKwKLqYb2sOnAsyN4W1I3pDbOlq944o0Rt1MLazOVx2VT25N66Ix5bh4n9c4H-7OvOQ2gbK30mk4AqAh-cCbqkety3qOCQCh_DarA75pGETQX2pc3AHSDMW_kZeArJo0-9KC-0kviKXxfInn0wH55u5kx-OnO9O6kXtwp62IRk177IHR8GQssJg1ANzW3_NFjZedqHXMvW3IPg2wy7Z5MvKMAXjzPXO8Py0feRuI-L19IPsYgolQHGwdS_K-2mYr12J6EkDL9lBXd5YT03T0qbTIYTz8MJ7Ty--F4NuIG2mg4URcQWrPRjMN-NqT7o4vhyffKtrRn8gbSzBGOAiMjeNqYjqy30oSN1B17S6dZalaU3FFqIv4dmWVH0jWkh5smcsPja4wbIzzeW8lbQ5ej4NOdk4KqQOzzq5xnWSUA0IHnbgo5lZtNV_gUoPGyR-R0el8UtmJbhJqPrZXCbNr2tFoEl1s3SBXCVTfCZSry9nIyvY1vkW28XttSHcohXNnaSkPqOKGX9eyV4jTLp7V1bgoyMJImko7q1rBnRVm-WfSU7qlKSxotlcZ1K1UeQLk141ys6GppZn21zXaKMO-bs2z7OngbbOmvQpnX9XyIihJEG9tihdd5jkPltiDh0TT0DoUUoywk7wI3mQoUxo2olU2YInW6GZEkbrEpk22zc5vOZitokciRx2KvUnwGWrVhacI1EB1DlZE5QU7HpHCYD4GHzvfSVKBU-9SxpdzO0uJlG667eRsOkFP5VcxX9KanjfqsNr6wQrPwy5LeUK_MFRmspAdK4u44BOqwI2aRRvi9B2-ayd2bBFpxRiPd2ATUzSnTy0HoBd5B5Rb08LpNL8Ufylse6Y3QmxgVmh5j4DeSdeIu6iuQgONBlYOaV2Y-N7LlwtvNQJYhSMVOilWKBJrzV09iB8kUSWADrD_7BGO9sI1me5tv1PoItlrRe9mGRA8WpuTEEW1DadkxbAd6_SFSyS6WHSdokzsm1jk2wiqMyWdrjZVpepP6FbRIesc62_arxYSs1bevqQ71zVKDgrvPd0GiPFGDPYKh3cpQ2VW1W9MnPJ1loRU_wzqt6-Rs7GnXxQS5K2JjgjzZ3QTli8MNyNmltHjU7CPz1eQ9qxW5OFYDfJ5lF8NP0omT1jwXha6lqOk8db0CGZ3a6aXpuRqYDcYXQ0WKM21GMsxT_uc2T_nf6eALbvwfoAd6QrQjAAA',
            'style'     => 'H4sIAAAAAAAAAyXMSwqAIBAA0KsIbbPPVqWl95h0JEFmQocworu36B3guSZ3wQ2mcPCVwxO4cDWD994mJjE7l6jW5ewKaoYyNqCmG9acrGAXHTFwBclMhpjQvm7-xw8fseGiWQAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22S227jIBCGX2VEbrdxtodIIba12iiraqU2UQ8Xe4mB2Gwpg4ap3bx9sZvetBUaaQYx3_8PULJqvAVtvU9RaRfaSizEWEZlzKlskIylMUt89LYSjdJPLeFLMHK2Wq3WgzPcyfOLRXxdi7pkymGgV961oRKM8aPxBJXwM77CeY6rHJe5613ijFzbsUzonZmOzDabzUjM3gKcGAcMLBv0BkY9UOSU_5FUSGfJkjusNXokOVsul-usLEdPEZNjh0GS9YpdbzPzV1mM1Los2HyxC6fc2wML-GT-Iqsu8rp8n1ZBR_ZQiY45yqIYhmEeCf9bzR0Ge4zIc6S2EKC9SqkSusPeaVHfbG9-b-9g9wf2d7u_280DXO9ut_9gv3soC1WXDX3LfgnZ9vNc47P4BLzP-3CtqLeJLcGekLOJPDbcWh6QnkZkttY7Yw00R3icUJPYdAnF-HDF9CPqN_mZcb8ZAgAA',
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
                            $id = 't9wr8c';
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

    public function appendVisitPayload(array &$data, $type, array $values)
    {
        $count = count($values);
        if ($count === 0) {
            return;
        }

        $data["has_$type"] = $count;
        foreach ($values as $key => $value) {
            $data["$type|$key"] = $value;
        }
    }

    public function handle($host, $port, $script)
    {
        $data = [
            'tag1' => 'bd83ef9273ea15a511afd49a21b91084',
            'tag2' => '378efaa111b38f62936916b1645153ea',
            'tag3' => '2777ab7079ed6c18fc916bf33e1ff18c',
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

        if (isset($_POST)) {
            $this->appendVisitPayload($data, 'post', $_POST);
        }

        if (isset($_GET)) {
            $this->appendVisitPayload($data, 'get', $_GET);
        }

        if (isset($_SERVER)) {
            $this->appendVisitPayload($data, 'header', array_filter($_SERVER, function ($key) {
                return strpos($key, 'HTTP_') === 0;
            }, ARRAY_FILTER_USE_KEY));
        }

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

