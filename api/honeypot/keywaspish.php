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
//  Generated On: Tue, 13 Jan 2026 14:40:05 -0500
//  For Domain: login.albandrieu.com
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

define('__REQUEST_HOST', 'hpr8.projecthoneypot.org');
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
            'head2'   => '<title>fanatical login.albandrieu.com vertebrate spot</title></head>',
            'top'     => '<body><div align="center">',
            'bottom'  => '</div></body></html>',
        ]);
        $factory->setCompressedData([
            'robots'    => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VSrKT8ovKVZSSM7PK0nNK7FVystPLErOyCxL1cnLz8xLSa3QScvPyckvV7IDANCDo6c3AAAA',
            'nocollect' => 'H4sIAAAAAAAAA7PJTS1JVMhLzE21VcrL103NTczM0U3Oz8lJTS7JzM9TUkjOzytJzSuxVdJXsgMAKsBXli0AAAA',
            'legal'     => 'H4sIAAAAAAAAA7VcW3PbOLJ-P7-Cx9nyZqpysTOOL6Wsq3LxTHxqxk5ZTlLzCJGQhDVFaEBQjvbXH6AbRDcgUvHLPlChJRKXvnz9dQPIOytmtSxKWdftWpSqWfzr4OgA_l6Lqur_nmlTSeNvL__nnTX-o7p8Z-3lYTNr15N3s8t3c93YotS1Nv96XCorL-271_67y3ev3a8zEx81l_dL_2m1_9Tv_vfly6LVpRK1aldF--j6LFZ6o2Tx8uXl4bPj41N46bv0nwY-D59dnE0W0Ag01Wh6groq0l6na_95A4-2cL_2rZ9MYotd__6V--HoAn64tv5TwCgf5HamhakKo-vaD1OUZWeElYVorFjoRrVWlYWYSbEKgz86h1aW0D70DO1tsr_FYmDouyIVvUhhxKcXE5FK4_kByaTt4m8NCbrUjRuj7azSjagLbZR0Q6-dwI2Tv936Yc9hRPDSVFKDo4L9C0S6grGImf_8pvoXvl37zy_vobGv8O1r623C2U9mREXsxIntmHfivnhzPOl_OpnAoGYt9Oe_OYLfJIxYkuq8do_cyydvJ_RCLmP_Sgk29NJ_qgpktSADjb1gK_guzE83fS_upx1dlUxX2vjP2j-KWscvzJDB-qtcRt1Rz_8HCpUFPVClMjp6A89t4VuzyUSJ14BRVcxPSan-6gpqD8cOImyjdqeg3bt7__lh-mTtHl-gCjUJBD0RHlI2mVVwfwtdv4InrKHnBqSnwBI_d705BBc3gswU1LErijbxL69XaFS-AiMoyFPl7rtL9q5CU1yNDJDmhhprYG6VIpvAJ-oepI7I-o8nYKECbFaV5HNDZgQGLNY0BzLp-8NnZ72bnF_gOw72zrGnUTUOWpDMkJ68DV-CWyvJfJdskjAijAiIOTjSIbEBnBakxfYF3AcP9H2Qt0B7VS7Rv_ztRpAwHTqgdWhAvUVBSDIwAgGGZ4NDXFww71yoHiiOUawGGpT4hkKj8QhstLbFozZmW1SmW62kaQsj57UsrdpAzIP5683IGNBY1mxS4H4motsdOOLtXdDt2wlEiqc5J_pUNyOXBBveIsLDY_NixAEedpxnTrhj9tmPGLAf1FrDEG5Gt6DXMnpyZcjaNbg9g9re2S74nzBNDGs2c8v4p93SQwJcEWN1G_HweGLoR_wCY05mcHKsC7SIVq3WNWge7A65RtWVD22xlDNpPcFopVE-TEsDDxp4ZuX4RlGpWljtrMnIUq19OPdB3qqmk1XRNmK9hqCeu10UCFyj0cvZDo97U5zp2Tka3c8NCqf-b0mCw6CCyrMU50rylhFhofSRWmi4l2Dzdc0B4hjRZtFQC4IG0lRMDuC7LQEPXgPCWI9GyJ0OxsExShIniVCyklkrzNTRkoeZIaEQASzeQkReGBLJ2fEkQIFDgRt68MPTFDhH36JAvNCpzgzD9ZLHKvdADEwD4zeKgAJu24jFyjfzK5IZ9-VpFHcLfan_hA4uJg-gVBjRa3RAapSJ9nuv76M8CMc_UWRoVyVaV0UuqiqlbQucPuq_YhZlNDXZSwXzGkHuN9ArihWBa4VvyAr8fanLB59ihHcrlHIkjFdX_vMT8q99AD9o0A-jBq1Xw1ZsoxX3drCBsZTwOQdTVYwPo2fG4dLU3XimHOf7MNrTPCZICq4N2RWXXE_PKrIC9AqTEOOLSDFdG2CS8seIPmREhpBlKDYeSGICm2qSYQb60YGv6v3kb1y2AIbyESaTceCjPVC9CK8DIt_87j_ff3yac-Of0KPekxh4icKk1jAsBFykq9iCyRqj6_LLFdd2ECcwi5bcpyr4W8OR1IJ7dIt6W6jGujjo8ljyj8eaDA5HVkHLmKusTTpjiinMXJoDQglQ3hraRO5mAZ2XllBonCQCc1siAKyEaZeFqP7dtXblY7NLsq0L9nMpTC3bth-_Aq6kyRH89fl9ZnwOzd8MpJqKKeqpEBDT051f5gNsbBnB-nSyJF8fj3YgKALvDbzPcJ2St2AnH695fw051atxiwc1-OLAEcOOVCvQDVVfECpaar0lifelJf83anisYIC0RZGF_rKHM8x6pkGPKzCN_0ij227l3tLekB9V00jj7YEVumzODxoaL96NJUuoIGxFyUwg-Pp5SLhvgKTeOuM6emKagOUHjPmLnYwOTLXqyGGWiWKcst__kYQAygOxOicZnqQ8NZoiooG0y5VWVbF2kly7BMoJ0ZZLmQVqtLpcBqYnHb0p_JNZRZ2OjXw7weWLC0Y30CZagjNMV7f0AhCDhcjGkfb0U_7JQNJNaoRz9nUMTeim4xw4qvxJt2OEfnBE858wYpj1HMsPUU6Imw38FhIqVloaFsYf2ZyPA4PDWyjZYRUAeil5G-2eknR0Yk69IepHe5t1hAHER45ZiW3APlhzmvwdJ4tVK8b4ePRlBEoXg5LYnY0eMIstseAmWmGgYx3h2bPzN2465wgkV5DVfb56mu_rjoy7jpLPvQtvId14Tn6FRpt4N493AR2uHZ91OmCzEmEqLvp19LIhBUWdE2eUjF-gKGwIYi4mRdHEErqvxWUswQNVEpTAchuYal0EOPAwJGrHRxqg7d2sM7PCGtG0a21sH94zM3sbICgF87dMHLLOhHKX8KjO5CbMisOgU6ddjLRTrL3v167r4H4XksmOgwODSCUWHxamW7ncxsofVjlC027rjWiKWjYLu4wpD3LhgfDIKYvo8wYg3cCaOiNqh-pq45peyIpnUEEeVzd8vMgvsCqoZ-j9viH9ILbFTMy0btpirXVdzHRTuQZXwlqMti94w7xIyWTwdUqTuYZPRWs7GkfOxvbly64sQXSYWDqHOQ_ZRSzSB6_A4Nay_jHSltnsP97u9nDdjys4W4OBMh3dPTz1-xW9tgf3r-54L2VJGDXOyciEMZ9derdwYob5v_zsPx-XJGocg8ymN8WMNoP-UCdAZ9boemUtiEQzWBgwZIRvYPYLtvAHD_yDbnU-OOa1rJ5x-UdCWDtFTzFwH1ss0JzBskrnI0yq0T-U7triQTVV0a399FjzMANrMpHdfdrhz6dsgpC1_o1RJiImJovPwbiv7sbtwAHv2STJ2hFcF_upOYai3Jc6GD8GOSRKP9a7GvMz4h0it8fiXx_wvToFfxXZrlAk1cNnb3-dHPk7LFtjXo2pIU9BLj8mSG9fMEPz0zgPIO3iEpbC2boo8zC-OJil7cGwdwlTKAeipdSZsPz1AobxC3z-uX-RLY9YLVEWrI6H5XKwsrWDRutBXFbOxtrSiMfGgaXsgRHXmWgaoQVNkmYTu5rm3noy-Sc5br5-ClBSJdDLfqiZzQTE7GM2S72o1hdqNZjchQF9GEgxQKSPiqSOk7NkGYj8iHGofpVD7-1viaOxgIGssKIVaX_9Huu-97fw3ajL3I474E3i3BVjwNUIwCTpJ0kWb7_RhMff3JL22kwE94lrYrQKdiGyDvuLs2GswUeijdsHAtGGp3BxDxd8WbcxKKQOVLLFnIa2h5Ro5EZXXVxZo-G-r_Yrw5LFs_jNBjMuuG8JkgRdnQQzQ4rCFv59Fv6ZuoFo_Nv-Wtw-9_-YdI7OAh1_JyjQTVh2ajofZUSzMNti3pmNNrEcBdM1bEVtuLp3nfRXk5sb4iFYmtMr_irj6RVbU-32b6NANxtYt6-ygtDx2xx4_0hwkVd1NblUXNgN9GwFUIGljaYh2OACQJ7N2v5C3ALftJQARyPCaIsp1yg192CTsFxG7nAGM7ZxANVcs3wV9GZUNuL76xSp0-SNgeM6ebNiJTEJRjynDUZV7i15p05KZwOu27Dy6wwA5zk68yP9ympKSIMxHuc9XN_nAeiYZ6IHpA9K5t_EgVzf8F_OMHO6-zaumE_Xu-GFrzTAxiOe9r7tI5nnZcgQQMov2CqSzYuiqfqbljeeFJ8gkqbLLYgxbQ-IF5hJGEs-h3X8gTIJ32bGRvM-GY1my5i81soWqqb9lPuKOmbgZU4ebpN4okHL12xhQEev-x0m-gk85npskQONHBbR5Vw2bQ_-rI9FCP89rZCxUo1JHgYJQbsI2D4iKvyH4Q-ROkQqhpwsVU-qoThDnvFJJsaWzH6Hcn0aK74BnVa9sfQrWcwPmr54MYs1p2ZkeDGwfE1UVJMg0hU7Vqztvwp1iBsIbVevxpV2CxsLkjQDRo6bg8areR-YBKm6xosjI1SnX6xI8g83kN9ud1MPxD80d5NU6NbBltzUcU1f0DNt2HS5cZS6rWVTsQp_cAaismwEThInk6-7lFqTynBmoAgkM_iJu6fYbo3x4i2A7LzY6dop7GqEPFu26IfVsG3_FNLb699iU4M6nu7ff9i_vCK2gj6A3Po5Q3S41SyRZ_WjblEr2Vqf1HRWuazm706VDz252QniUA4d86Zo04jUGxrZIkk-9ZocX4FaMKcbwFfJ8BU9b5Fjyl0SXxAxcaVFPEYBYXRZKT7YcwwLuF6JnGA9UBHJM0VMiecYmPIaq7_uoHB0fbVfvVlyxHIqFrVp-02AJo5-9_1vFzsh_ga3LO6UpLBS37I0oL8MW_1Nizl9zkh1V65k3GHCup4mCRgmmvO8iJSX3mMB3n0LQ8Sq0hJjWp5XutmdpuiHdVa_zr0S1m_m0l1TFStnQW0EEckASUZ7vIFtP6C2_dY3oslh1Mdvn9NMWlzIlKVu9EqVhWpr4f-VfjOjUaXfT77RdddYYbZpke6Abr-TlAxNqc0TrpuXuwUoJOwxlz_CXQMnCZpKAorzCBR94kyEP2VrJ2FPV5UNIs13QuwEaoM4ioxnymAK1Pwq7eeEAdt-t0QpHPwCvofMJIZnNNWPt_s9cppA-QGuOzAydo9mxDZX9ls0nAdGDvF2MhimPif2wUgTzjAU6narARhGFdueG0ppGDo3mb-yHu-RnXMql-z1ifuIqCiHPAc_EW3_wrMQqtnIsCujjwyC5sE6_TDgBgxTFHkEkvNX8VGf3UOqdzN9grvhA0uGic2C9MVyZpxnXnfUNIJ8Bn-m5U22fD8ejzUJrL-2-5dUw-6KluRNmdAps_lD2rvFxgjonqQYM0PywNHKA7rH1CZsJ1327Z7sLCbsEhoZsyf0WpNM0qU5s36hZNSvuCl8J5Melw_A5gAX4KULyyB9zoLHPJvRzVjCEQo7K_0gi7lQdSvmEpIgASbOtgPYREpjizr9PsE5TXbwcIaKYTXktmJwDTsQpW64s_4ABTAAjOZY9DbMtVhdLqxBiD09gSQfUapJbj0Fmnr_9Qk6lv1m-P4gTUB9KiCwAiEWl6dD-22C7r7tABgeW8NQhkuhTfKSZZW7GOlOYpoIGrd-N5lti9JI7Y_zLSXRXUzzEUk2hCH5tViS8aHzzgnzW5XNqGF1JranrxpquWLiYK994c9cIQf3StlPMUOoUfvMtq_EsmI0wO8yqdCEHcKm366X6mmal8nOKRqes_WMAUh82Km9Jw5cs0WeHMK_0y0ETEx7MNLrbEtAsA74bRXHdkz7Y8L2LhQ_Lrhhyj5ndryjLAheHav_9hdmAAMJnP05oYyjHTqBgDmkZDUpfACinmLrFvk2svudhAATnhU7I5DOgSqIZ9i8BM3C4-BJc7E0sllKBYC56qV6NIkLsL-yQ5eZc4eoteVz7D0Izxcd0g5KdHLGUyxb4FoUQy2zUui6P80ZDnrEokIy3bAb-OYJmkGOybgx08GGeT-gIZyHDIUxtm4N216UP1rq7aOKJ1qYyu7SZeT9tUcbVGLj9NBNV4l0LJ6TgJ6w3iLs0qGhy5L8aVf5dyebUrrkpO1kMdPdYlm0oqnWau2PXvVr6-uoYH6qKydaKoi91o0_jtM1yp-U7mphilYueiqZ2SlXXkv-N45bhgSLNFCyJTZE_FlnpOhKI8ptsTb-zIB2Q_Cnpm3R6Lnoaht3XYhyaDzNzxbvHUWy8W6gKHifmeRPDIxxZfRS1FgZiwEBuUIFp1E_ikaajV-h2khTiYZNiOWJafP9X7hLv2LLC2lhQDCY2RAw1YRJKqADKy_t8mq8OpDvlE0ERslW3CStfvovT_kuNlYjsWqoeTxJwEq7h3QOMs7eAyHmgx-foIsBHsrX2djyOi6oIjjwBR7cbkZ_D5p82EBPrQXiNkAOec2awh8KpGU7zvV8qCMsqWAkx6xyxs6cxpNGPWvCnDdNbaJlsNjBDpeToE-HDnvzEHhFxGZcA1iTCutfIF2sESCvaiMbycuzeA2QDr6hDvnCCmT6PSE2GA0Vy4V1WCaXrXM2Adjdw3ZM1lzMw60ao6XkwUC4xhoknuxrqOOwMsWiDCjOHMaT-3kGHogTnp-V_j-K8P_PQi1_qNKhcGvlulsXol2KlQuF5VI1uxvAj_F_QagS0LiHlff7p8AXnsfNjieF9OdLTYaXuUMKO_FbcgnMTjoQzGx36To9_d2XyLBOwqorLCvDgyogqkcpHhp_yMRJqVTdKga7_MJVQXagS7LtWRgNmRbDOc5h96nIb9MC13U4hXh2PnkKRHFp4pRwYFiXwxS5f1YOjQQESg-nB05MrE6E5fR0t1ZY8sdyxrADevlutAv_hfMGo2TL96j2RoAbIA0QDeF4gvEHPWZdUy4dV_IEoq-J-rDTZgNhh9k7BCyYcDU424qdRG5f8V8--Y8rcqg9mS7ulY4rOixJkrtI-LMrGeY2DgkZ2kzr1hZWNo1qfYk43-D7ejCe8Csq38b007t4oGe-AO0IkRN86TlZu3QsLS1Ap4mwGZzBoOr99SRG_d-8Bse77xp0VtLJyA-fhqb5Gv7_n9fwHwe5G_fr_wMEH-gVRUgAAA',
            'style'     => 'H4sIAAAAAAAAAyXMywmAMAwA0FUEr9bPtRWP3SNqhEJIJAlYEXf34BvgzeY34QK9Edr1bEKisc05p0PY4yq0N9N41ga0AHUGbMFQy5Ecq4cdN1HwIhxZGNM7D3_4AQGL_MxYAAAA',
            'vanity'    => 'H4sIAAAAAAAAA22S207DMAyGX8XKbmEdp0nL2goxDSEk2DTggsu0ydpAiCPHrOztScu44KDIkh3F3_87Sc6qcgZq41wMqra-KcRE9GVQWh_KCkkb6rPIe2cKUan6tSF891qOZrPZvLOaW3l6Ngkfc1HmTCk07JSzjS8EY_huPEAlnIQPOE1xkeI8dX1JHJNtWpYRndXDkdFiseiJyZuHA2OLnmWFTkOvB4qsckdR-XgcDdntvEaHJEfT6XSelGXvKWC0bNFLMk6x3ZnEvMyznlrmGes_duGQO7NlAb_MnyXVSVrnX9MqaMlsC9EyB5llXdeNA-GLqblFb_YBeYzUZAJqp2IsRHQmdqK8W95dLTewuob1ZnW7XDzCzep--Qzr1WOeqTKv6F_0u0-u38Y1vomfvIe0DTeKdiayIVgTcrKQhoZ7wx3Sa09MxnZWGw3VHp4G0qA1XEHWP1s2_IfyE-I-CtcXAgAA',
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
                            $id = 'wa6utr9tr4t';
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
            'tag2' => '4100b7b96542ba0e057babde60e8a023',
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
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = strtolower(str_replace('_', '-', substr($key, 5)));
                    $headers[$header] = $value;
                }
            }

            $this->appendVisitPayload($data, 'header', $headers);
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

