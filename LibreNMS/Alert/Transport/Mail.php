<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Mail Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Mail extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        global $config;
        if (empty($this->config['email'])) {
            $email = $obj['contacts'];
        } else {
            $email = $this->config['email'];
        }
        return send_mail($email, $obj['title'], $obj['msg'], ($config['email_html'] == 'true') ? true : false);
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Email',
                    'name' => 'email',
                    'descr' => 'Email address of contact',
                    'type'  => 'text',
                    'required' => true
                ]
            ],
            'validation' => [
                'email' => 'required|email'
            ]
        ];
    }

    public static function configBuilder($vars)
    {
        $status = 'ok';
        $message = '';

        if ($vars['email']) {
            $transport_config = [
                'email' => $vars['email']
            ];
        } else {
            $status = 'error';
            $message = 'Missing email information';
        }

        return [
            'transport_config' => $transport_config,
            'status' => $status,
            'meessage' => $message
        ];
    }
}
