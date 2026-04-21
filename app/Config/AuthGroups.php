<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title' => 'Super Admin',
            'description' => 'Owner Verra. Bisa manage semua tenant, semua user, semua konfigurasi sistem.',
        ],
        'tenant_admin' => [
            'title' => 'Tenant Admin',
            'description' => 'Manage 1 tenant: KB, API keys, nomor WA, user, konfigurasi AI.',
        ],
        'operator' => [
            'title' => 'Operator',
            'description' => 'Lihat conversation history & handover list. Tidak bisa edit config.',
        ],
        'agent' => [
            'title' => 'Agent',
            'description' => 'Hanya bisa handle (claim & close) handover yang masuk.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'tenants.create' => 'Can create new tenants',
        'tenants.read' => 'Can view tenants',
        'tenants.update' => 'Can update tenants',
        'tenants.delete' => 'Can delete tenants',
        'users.create' => 'Can create users',
        'users.read' => 'Can view users',
        'users.update' => 'Can update users',
        'users.delete' => 'Can delete users',
        'kb.create' => 'Can create KB',
        'kb.read' => 'Can view KB',
        'kb.update' => 'Can update KB',
        'kb.delete' => 'Can delete KB',
        'channels.create' => 'Can create WA channels',
        'channels.read' => 'Can view WA channels',
        'channels.update' => 'Can update WA channels',
        'channels.delete' => 'Can delete WA channels',
        'config.read' => 'Can view config',
        'config.update' => 'Can update config',
        'conversations.read' => 'Can view conversations',
        'handover.read' => 'Can view handover',
        'handover.handle' => 'Can handle handover',
        'handover.close' => 'Can close handover',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'tenants.*',
            'users.*',
            'kb.*',
            'channels.*',
            'config.*',
            'conversations.*',
            'handover.*',
        ],
        'tenant_admin' => [
            'users.*',
            'kb.*',
            'channels.*',
            'config.*',
            'conversations.*',
            'handover.*',
        ],
        'operator' => [
            'conversations.read',
            'handover.*',
        ],
        'agent' => [
            'handover.*',
        ],
    ];
}
