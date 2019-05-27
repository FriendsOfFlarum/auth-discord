import SettingsModal from '@fof/components/admin/settings/SettingsModal';
import StringItem from '@fof/components/admin/settings/items/StringItem';

app.initializers.add('fof/auth-discord', () => {
    app.extensionSettings['fof-auth-discord'] = () =>
        app.modal.show(
            new SettingsModal({
                title: app.translator.trans('fof-auth-discord.admin.discord_settings.title'),
                size: 'medium',
                items: [
                    <StringItem key="fof-auth-discord.client_id">
                        {app.translator.trans('fof-auth-discord.admin.discord_settings.client_id_label')}
                    </StringItem>,
                    <StringItem key="fof-auth-discord.client_secret">
                        {app.translator.trans('fof-auth-discord.admin.discord_settings.client_secret_label')}
                    </StringItem>,
                ],
            })
        );
});
