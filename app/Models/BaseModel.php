<?php
/**
 * BaseModel.php
 *
 * -Description-
 *
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

abstract class BaseModel extends Model
{
    /**
     * Check if query is already joined with a table
     *
     * @param Builder $query
     * @param string $table
     * @return bool
     */
    public static function isJoined($query, $table)
    {
        $joins = $query->getQuery()->joins;
        if ($joins == null) {
            return false;
        }
        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper function to determine if user has access based on device permissions
     *
     * @param Builder $query
     * @param User $user
     * @param string $table
     * @return Builder
     */
    protected function hasDeviceAccess($query, User $user, $table = null)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (is_null($table)) {
            $table = $this->getTable();
        }

        return $query->join('devices_perms', 'devices_perms.device_id', "$table.device_id")
            ->where('devices_perms.user_id', $user->user_id);
    }

    /**
     * Helper function to determine if user has access based on port permissions
     *
     * @param Builder $query
     * @param User $user
     * @param string $table
     * @return Builder
     */
    protected function hasPortAccess($query, User $user, $table = null)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (is_null($table)) {
            $table = $this->getTable();
        }

        return $query->join('ports_perms', 'ports_perms.port_id', "$table.port_id")
            ->join('devices_perms', 'devices_perms.device_id', "$table.device_id")
            ->where(function ($query) use ($user) {
                $query->where('ports_perms.user_id', $user->user_id)
                    ->orWhere('devices_perms.user_id', $user->user_id);
            });
    }
}
