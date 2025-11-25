<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('footer.footer_text', '© ' . date('Y') . ' DispatchPro. All rights reserved.');
        $this->migrator->add('footer.links', [
            ['label' => 'Privacy', 'url' => '/privacy'],
            ['label' => 'Terms', 'url' => '/terms'],
            ['label' => 'Contact', 'url' => '/contact'],
        ]);
    }
};
