<?php

return [
    'tenant_admin' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/dashboard'],
        ['icon' => 'bi-phone', 'label' => 'WA Channels', 'url' => '/channels'],
        ['icon' => 'bi-book', 'label' => 'Knowledge Base', 'url' => '/kb'],
        ['icon' => 'bi-gear', 'label' => 'Konfigurasi AI', 'url' => '/config'],
        ['icon' => 'bi-key', 'label' => 'API Keys', 'url' => '/api-keys'],
        ['icon' => 'bi-key', 'label' => 'Handover Keywords', 'url' => '/keywords'],
        ['icon' => 'bi-lightning-charge', 'label' => 'Lead Assignment', 'url' => '/lead-config'],
        ['icon' => 'bi-people', 'label' => 'Users', 'url' => '/users'],
        ['icon' => 'bi-chat-left-text', 'label' => 'Conversations', 'url' => '/conversations'],
        ['icon' => 'bi-person-lines-fill', 'label' => 'Handover', 'url' => '/handover'],
    ],
    'superadmin' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/superadmin/dashboard'],
        ['icon' => 'bi-building', 'label' => 'Tenants', 'url' => '/superadmin/tenant'],
        ['icon' => 'bi-phone', 'label' => 'WA Channels', 'url' => '/channels'],
        ['icon' => 'bi-book', 'label' => 'Knowledge Base', 'url' => '/kb'],
        ['icon' => 'bi-gear', 'label' => 'Konfigurasi AI', 'url' => '/config'],
        ['icon' => 'bi-key', 'label' => 'API Keys', 'url' => '/api-keys'],
        ['icon' => 'bi-key', 'label' => 'Handover Keywords', 'url' => '/keywords'],
        ['icon' => 'bi-lightning-charge', 'label' => 'Lead Assignment', 'url' => '/lead-config'],
        ['icon' => 'bi-people', 'label' => 'Users', 'url' => '/users'],
        ['icon' => 'bi-chat-left-text', 'label' => 'Conversations', 'url' => '/conversations'],
        ['icon' => 'bi-person-lines-fill', 'label' => 'Handover', 'url' => '/handover'],
    ],
    'operator' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/dashboard'],
        ['icon' => 'bi-chat-left-text', 'label' => 'Conversations', 'url' => '/conversations'],
        ['icon' => 'bi-person-lines-fill', 'label' => 'Handover', 'url' => '/handover'],
    ],
    'agent' => [
        ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'url' => '/dashboard'],
        ['icon' => 'bi-person-lines-fill', 'label' => 'Handover', 'url' => '/handover'],
    ],
];
