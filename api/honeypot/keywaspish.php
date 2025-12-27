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
//  Generated On: Fri, 26 Dec 2025 07:01:15 -0500
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
            'head2'   => '<title>Paisley Drum nabla.albandrieu.com</title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVSsvPyckv18nLz8xLSa0A0olFyRmZZalKdgCebds4NwAAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA51a73PbNhL9fn8FxrnxJTOu88tx4qPrGdVREnUS2Scp7fQjREISEopgAVCq8tff2wVASY5Fp725tjJFgovdt2_fLnTp5bRUYmpsoezPR8-ORK7KspZFoat5-7erZR7_vvrXpbf0r0I4vynVz0czU_mfZnKpy81_xdJUhu5W2dHV5fTqkr4UuSkNFn_0jv93dGUun9L1q8un06vjaurqTPzwf-Ifl1MbH6VPk4USazU9fvT8-atMeyVm1iz5Flw5z4zY0IfnWSNcM_2Cz8-eZ7m_37hFNM7T88Yvwt1WeGUvsbMqbbvQri4ltlyZSh1dFWqlSlPDS6Iwxl4-pXuvlk7MzUoFU968ztTUkXmtOTLP-UurZCH8Qi1FLq2aNXRNzlXl3f1G5tFIQ9a9yEQyU2ha-Vm23TvWPBXwz_3r6LgO2eGC--hRbOmn8LiVlZspe_zo4nUGoNCXv4dNnNLnbRg6Q3cQCe4gEvDImK7uB3pMVr3IYgS1LAVdOMuU81bnPmz79ZsM9n9olmx1JX7TTkcg0A0urKGLGFl3Avj7sPmT-80sopm6CwGumcFTyoqvaqMKUStbK9_ARKvmVvlZU4oFckNzxkV8kD21NXMrl8EA2s-zZ3GDTs8rdrlsvFlKr3PeUVkCP2fnWQpAfylpf8_eZAJ5a5XrsnLRLAvbLAXeuoJfTBUtccpxUg280A4m5wGi80p_UwXbUOL5rpXxYl0AshSVL0bDjeovOMFzzqnci1o6h_yILzwVfxhKgK8VR2UhnZBiJctGYb0Qru5gcCy9sZL2_jLrtA2GYZX5Rkx1WZr1JtpwImTIl-D1p8ayD4IVZnb_-2UiiJhzTgkZbAkus8QB4ctXmRNIl5DNVqkl3CNkVcAzfyN7DqTUbl70Zb6Af0My-I1gnNAbw6tlpAXnRWIFEYwF59Atj49-bQqCF9mM-PVyrzksvtOrVY7NKJfTthaqrMXUGllMaYdWraUttuE-xEDJm0gSHVLAq7CN4N1C9Ni5DbZ0kS1EyVtbAyy2G416mnKsk0FlzS4K_uGsnRm7DO8OqBBqy6lmJmbRLhWAR0mH7bpAPcQ3G8ChwzC4rAI9yDnCM5coBsT4Fpm7QP6sFCpo4xfRdEQhhKkizzZzpkBlVzpXnD73b20et5YjuSmEh1yfyrCchpfg5UBpl-21qrA9tzDrwsydUBUFOIqHUldKEAOAJ1LQgfh9nD9UD9QDyuCh5_OOerKbL0Q9cE8AHNDbtem1rgqzjjsKS3gjFrTMkiDwAmVX1qGoKGmFC95cMmwushYY08YpKsWxUg-IK-lLKk-JOgDreZctwMbSIIu9TLyNHBdVcvJvg_FgcjMadxfb40fnr7L3o35fTOih295o8oe4gT6BkBj3h28Hw_cMtM-48Twb9__3uT-ciF9G_R4uvA5l56kvfkwE_qAQWHwXuL__BN07gYkX2egTdvMyS_phG_s1Of8s0_miWw9tHsotFkzMCKD6rojJChoS-U21HMQ6t3qJO63UKYARDjNDhSkQMHKKRWFF2cVvcgKQY7GA2Ic9qAheEqaQmVyCyk7iaVAtl2SKh7QUVYPPpnECogXyxIm8KX1jZRntKjenoodsHwYtRcsXeqUDJ0LWPHZP7nfR1-ii9cJ0-3AbFqvni0NSNy0HjVR5EOedYKJeLJAVJJ5Jb6Rwh3-ubvvXg97HLURw6ePguj9kePT3ro920aITNwhTkSzp8mtTzUyDMojisTDegFaKBsQNNrVRluKrKd0i7UbgQrOsF62bKflNkutnmTsVw0j55HVJ6pUVeaH-UiRVLbTRBQSu71R5tVULTeJI56KwcubFn43C23MNcZCbpc6TAuK4iiSPO5aETNulQKY0hia06FmGvcpd36P0dDVLEhi0BXojpIRXJQSyrBewEXJwn2ixi27NXdclyyry7VoRjoSEVFaNFV9kU_nN3nrcfT2gHLxZI0lmtiFflRvb-mqLPFJ32P6BDtK1AjHmaEzxzrfWpJjNzEl04wsoCmoXmgpNZcWa1e2wxUUmGRPrUhXzuHYrYGRSfSWzBpYSn8fi36-ehQQmQE4biLsK-pZziTuGTidPLQzxYiZXprHkQEg-0vVeL6cqdboKyB10EqGOEqGxtdWOG2UtPWFRWMm93FRa_B-ctN0s9uRjnXQx3dV_oDzalqc7BOiHQovFOKX7N-h8QoItNGeS9nek-D8fW-DJ3u0tGtBzhPz1y-y6t8spHA2S_1BQDVV8pA3TvNAViZEqzSVazceJf_REBBSBM-CiGO-5WXHwlrrSofE4y2R48g1qwjVTSl1q9G7dUw4jYmfCUlFbVSKPNPKuosJG11ijIvIoES3jcsfmY5vB_9aVO5AOicBjR7QUDF6nOhkiX2g1izDwP0JPS-XM0th60aZrCArIJfcx4qyTVBVWu39-8RAG_L2qg7VT__pm9Ha8W1PC-nJFWA9CEW4Ug2gAUoe8GKcQiioN0Z2og1e5DM007upUKnigqTjuL7NGlWFolRwmIDlTx6ejRqB_PvQmXb6c11QjgJ6cYu8hgIlX99z6gUXkCBs_z_rjCcN9-P5kpzr3hn-IT_0xV9ree5Zlj8dP-IYP4uadmHzoj_sw7s1Z1h99Gv8TVcmehxnDt3uV_PoGGnYyuBneEwynYlPHrk745RHStue7yDgbqJKzMgdqTeV4jOZiRyXSFEz8sgl5WsBnkYRjloBknXjM9BLqeCfeG4sWvpLWmrVYookqWiCTQojqoM2Eh0eXsN5bU0IySDSUhsGGbE84BGqKdsdYWsRJgE1zQBHaFxtHZ2BhNMfh3jsiC_EfDa4DBu44_erdzej7IFgwOMClKqAzdj5xhLLTCOkqL5tCBXWxhVUeuQ7Fz0bpsZA2jFiLE8FTAhOCGOd8gfeRXwcyqRWYewImvi3pQPS1lhSeNSwxyzhpo6nZA3JiLjcs-AGQb8z9CpzcNZkw38njqPhl11QsiWpFkyCVZALzQZi00dzi-esY7dRDWOo3A6LdgSmBartG5ErRtdMV5K6eUyF3huo50iNutGhRFUdmHNDOhFiaAgutjf0alAIKXaPQiIsZxIilrxQaptlsRyqA4HGdsxABJ97XYQISAxXmmmYeykBxugNh7nQ_7jcEH3u_7_3du0Mzv34eDb7vMCW_NU7XrKiDvw3jkkG6raGdcTyE1jT9lKyhwuiTvaksjZWmm-5yQdRVyjUzlpdsVUAViCpNJo56gSOXofNA3Z-qcNNagQV6RfiCKgvIlySIYJb3EaA0m-L4Vm2vKtolKzH2nGhB8yg2gV2WHBObIXzMo_KJtLOKVw8ANWm-dCZS30tWN-_2gkgTE65B-zxFFf1VNt4HxJ6ai2rNi9sYYR9ibErW4thrkTbynJ2rxBEz5O6Av4rVJVgcPtNTG4EWJl8gnmBsmkW0Oj2kMZDVPU1Nk0ibzH3f413CAgiV0YAPSVCux5MbgBjfT26oIIvB2_5wMnjHhva3oEa5HvdZ5Pw24BlQ__Tv1Wv26OuLbN_5n_cdHB17QEYm4D8-enCgGWtzAKmPAmvJTE1am-G3ag-F2pnLY_qu2rAqQ7QmnbS-QgekppYE0krNlQfzqD8bXZmcDkDScA4vRZs13qmbJB2CRdPU0ESMuEU83QnNgrGRRNLE3oaCrFE0l9vQTFT7EhvUf8wSnk-Gz85xv3eczgpRC-9kxfBm-NOHz596w7tJcJ7xSHEX-XkeSdzxUu1uQiM1DWXGs7MrSNFSB7pwnuYX3ao6sSbVSeNDYYrhwzOd3eXUWNSeqnV990x3BfqUzDpf5LdvAuIryVt1CH-JmGd2z3nH7VkFeIqnEDrp--52RaIRDgdCubHoXCQR7VrCTWj90wwORSz0r2aliyRdZwEa4NVmBwiJdaxKgQkHTzQOuHdDdVsSIm_zU3k4OHdNp_XSwtco8xCRoqZ5STR46rzckhqjOQhOVHBa9iSItUDrHpX9ACD0niYL633bCnQ4QR4YFya5IsVKmzJJpn20j99GJgaTUZ_QtVUl51BJaCtL40y9SOOk73lLhbMQOqZBZzxlGqGfIbBXY5Zuo4WEpevUutMgiU8buSiO45WwWZSMs-fhUPhFhrCHzptraKINOnYNPolzkpBEB-bXKdO2hx9xyNIK8Ug3nHyo5OCAiyycljgCK3OY4MlJ7JsgOBUzUDDO0Eywm5_p4IvOBnfUyy4X7cus3i_3lgnbklA0I6dTqOCI03BmGJnj6InQM8hF4RpXhx5npwenydatNWEGq2RwSmzkWoU4fB86jcmoNxy_C5VwFI9JRuKaS9twPBlMPk_6Y9I6qK7tjv5GkQxPxKDTKIDyflcwFEEJxMpF_EUDnFCzgn-WdLxon6R2LpSETqWty1Ls_iYh0vvRk1NxWypGowuXtm_h-B_gldlWgwA5e_0Tt8JfUw_QxGUj9sNgNtp8kWEXAVTqDj5G97T1KgxAX2R1eUAA5-3AK70C0Sd9dUK_HAqV2YpWED_PlnXjdxvkhKiLTOz0_3dLwTkdjXCbCH2wOY29P8RGrPXGutjoqnQAeZ6lARpTvS6pMAUAnmxfqqt4PJ6GyskzdLpERAoYHMdfMWwN7gy8V_mi0n821BEUWlF3VDclH0JXap3zWVFh8Wdi92jVAz9J6mTTv9DU7_zqpJUk2u1Hebelck3eecQ2pYmJLpz-pvZmVLVECJiGSRxtu4d24dCV4mKhqkNdRapEAaMRsO1vuSKOqBqxaEuynsHeOW3QVSTcnWW_NGjqKLm5JTreziz9gYGP3tFN_KOOlUKXDAXCOcGzvzfU5bNCZCymdqvzKILOIPJSySr8ksU0jg4kTJnmFhXNHAtl7w0ZQ4QH9OUDHWw8eSBJeUBzpWJeG88_LAqdPT8RvHPgFX77ilgd-JhqXwhE_XwjesFNHwajt9vr4az7un876Q2v-4Ka_31Cf8o_g3zKzIkP-OL_jWawMUwpAAA',
            'style'     => 'H4sIAAAAAAAAAyXMywmAMAwA0FUEr9bPtS0eu0fUlBZKImlERdzdg2-A56veBWfoz0PTESVrelYuLLYNIbjIpHbhsjXTuF8NSIbSVaBqKkqOTvFSs-HKApqZLDGhe_3wrx8sPnEpXQAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22SwU7jMBCGX2XkXmlTClupbhKhrYoQErQC9sDRiZ3Ei_FY42lD336dUC4sskaakT3f_4_tnFXlDNTGuRhUbX1biLkYyqC0PpcVkjY0ZJFPzhSiUvVbS3jwWk5Wq9W6t5o7ubiah4-1KHOmFBqOytnWF4IxfDWeoRIuwwcsUvxKcZ26PiWmZNuOZURn9XhkstlsBmLy5uHMaNCzrNBpGPRAkVXuIiofp9GQbdY1OiQ5WS6X66QsB08Bo2WLXpJxiu3RJOZNng3UMs9Y_2cXzrkzDQv4Zv4qqc7Tuv6cVkFHpilExxxklvV9PwuEf03NHXpzCsgzpDYTUDsVYyH6A3eHhix3onzYPvzePsHuFvZPu_vt5gXudo_bV9jvXvJMlXlFP_IPPll_n9X4Ln6APqc9uFN0NJENwZ6Qk5k0Pjwa7pHeBmyyeLTaaKhO8GfEjYLjZWTDA2bjzyj_AWKwkD8hAgAA',
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
                            $id = '8ex5swabr2';
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

