<?php
/**
 * /classes/DomainMOD/DwDisplay.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2017 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
//@formatter:off
namespace DomainMOD;

class DwDisplay
{
    public $system;

    public function __construct()
    {
        $this->system = new System();
    }

    public function account($server_id, $domain)
    {
        $result_account = $this->getAccount($server_id, $domain);
        $result = '';

        foreach ($result_account as $row) {

            ob_start();  ?>

            <td align="left" valign="top">
                <strong>Created:</strong> <?php echo date("Y M jS", $row->unix_startdate); ?><BR>
                <strong>Contact:</strong> <?php echo $row->email; ?><BR>
                <strong>IP Address:</strong> <?php echo $row->ip; ?><BR>
                <strong>Hosting Plan:</strong> <?php echo $row->plan; ?><BR>
                <strong>cPanel Theme:</strong> <?php echo $row->theme; ?><BR>
                <strong>User, Owner:</strong> <?php echo $row->user; ?>, <?php echo $row->owner; ?>
            </td>
            <td align="left" valign="top">
                <strong>Shell:</strong> <?php echo $row->shell; ?><BR>
                <strong>Home:</strong> /<?php echo $row->partition; ?> /<?php echo $row->user; ?><BR>
                <strong>HD Quota:</strong> <?php echo $row->disklimit; ?>
                <?php if ($row->disklimit != "unlimited") echo " MB"; ?><BR>
                <strong>HD Used:</strong> <?php echo number_format($row->diskused); ?> MB
            </td>
            <td align="left" valign="top">
                <strong>POP:</strong> <?php echo $row->maxpop; ?><BR>
                <strong>Lists:</strong> <?php echo $row->maxlst; ?><BR>
                <strong>Addons:</strong> <?php echo $row->maxaddons; ?><BR>
                <strong>Subdomains:</strong> <?php echo $row->maxsub; ?><BR>
                <strong>SQL:</strong> <?php echo $row->maxsql; ?><BR>
                <strong>FTP:</strong> <?php echo $row->maxftp; ?><BR>
                <strong>Parked:</strong> <?php echo $row->maxparked; ?><BR><BR>
            </td>
            <td align="left" valign="top">
                <strong>Suspended?</strong> <?php echo $row->suspended; ?><BR>
                <strong>When?</strong> <?php echo $row->suspendtime; ?><BR>
                <strong>Why?</strong> <?php echo $row->suspendreason; ?>
            </td><?php

            $result = ob_get_clean();
        }

        return $result;
    }

    public function getAccount($server_id, $domain)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT a.unix_startdate, a.email, a.ip, a.plan, a.theme, a.`user`, a.`owner`, a.shell, a.`partition`,
                a.disklimit, a.diskused, a.maxpop, a.maxlst, a.maxaddons, a.maxsub, a.maxsql, a.maxftp, a.maxparked,
                a.suspended, a.suspendtime, a.suspendreason, s.name AS server_name
            FROM dw_accounts AS a, dw_servers AS s
            WHERE a.server_id = s.id
              AND a.server_id = :server_id
              AND a.domain = :domain");
        $tmpq->execute(array(
                       'server_id' => $server_id,
                       'domain' => $domain));

        return $tmpq->fetchAll();
    }

    public function accountSidebar($server_name, $domain, $show_heading, $show_url)
    { 
        ob_start();
        echo $this->showHeading($domain, $show_heading);
        $server_wrapped = wordwrap($server_name, 20, "<BR>", true);
        echo $server_wrapped . "<BR>";
        echo $this->showUrl($domain, 'zone', 'list-zones.php', $show_url);
        $result = ob_get_clean();
        return $result;
    } 

    public function showHeading($heading, $show_heading)
    {
        $result = '';
        
        if ($show_heading == '1') {
            $visible_heading = wordwrap($heading, 20, "<BR>", true);
            $result = "<strong>" . $visible_heading . "</strong><BR><BR>";
        }
        return $result;
    } 

    public function showUrl($domain, $text, $url, $show_url)
    { 
        $result = '';
        
        if ($show_url == '1') {
            $result = "[<a href=\"" . $url . "?domain=" . $domain . "\">" . $text . "</a>]";
        }
        return $result;
    } 

    public function zone($server_id, $domain)
    { 
        ob_start();

        $result_zones = $this->getZone($server_id, $domain);

        foreach ($result_zones as $row_zone) {

            $result_records = $this->getRecords($row_zone->zone_id); ?>

            <table><?php

                foreach ($result_records as $row_records) { ?>

                    <tr>
                        <td align="right" valign="top">
                            <?php echo $row_records->formatted_type; ?>
                        </td>
                        <td align="center" valign="top"><?php
                            echo $row_records->formatted_line; ?>
                        </td>
                        <td align="left" valign="top"><?php
                            echo $row_records->formatted_output; ?>
                        </td>
                    </tr><?php

                } ?>

            </table><?php

        }

        $result = ob_get_clean();

        return $result;
    }

    public function zoneSidebar($server_id, $domain, $show_heading, $show_url)
    { 
        ob_start();
        $zone = $this->getZonefile($server_id, $domain);
        echo $this->showHeading($zone, $show_heading);
        $server_name = $this->getServerName($server_id);
        echo $server_name . "<BR>";
        echo $this->showURL($domain, 'account', 'list-accounts.php', $show_url);
        $result = ob_get_clean();

        return $result;
    } 

    public function getZonefile($server_id, $domain)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT zonefile
            FROM dw_dns_zones
            WHERE server_id = :server_id
              AND domain = :domain");
        $tmpq->execute(array(
                       'server_id' => $server_id,
                       'domain' => $domain));
        return $tmpq->fetchColumn();
    }

    public function getServerName($server_id)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT `name`
            FROM dw_servers
            WHERE id = :server_id");
        $tmpq->execute(array('server_id' => $server_id));
        return $tmpq->fetchColumn();
    }

    public function getZone($server_id, $domain)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT z.id AS zone_id, z.domain, z.zonefile, s.id AS server_id, s.name AS server_name
            FROM dw_dns_zones AS z, dw_servers AS s
            WHERE z.server_id = s.id
              AND z.server_id = :server_id
              AND z.domain = :domain");
        $tmpq->execute(array(
                       'server_id' => $server_id,
                       'domain' => $domain));
        return $tmpq->fetchAll();
    }

    public function getRecords($zone_id)
    {
        $tmpq = $this->system->db()->prepare("
            SELECT address, cname, `exchange`, `expire`, line, minimum, mname, `name`, nsdname, preference, raw,
                refresh, retry, rname, `serial`, ttl, txtdata, type, formatted_line, formatted_type,
                formatted_output
            FROM dw_dns_records
            WHERE dns_zone_id = :zone_id
            ORDER BY new_order");
        $tmpq->execute(array('zone_id' => $zone_id));
        return $tmpq->fetchAll();
    }

} //@formatter:on
