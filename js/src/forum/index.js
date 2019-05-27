import { extend } from 'flarum/extend';
import LogInButtons from 'flarum/components/LogInButtons';
import LogInButton from 'flarum/components/LogInButton';

app.initializers.add('fof/auth-discord', () => {
    extend(LogInButtons.prototype, 'items', function(items) {
        items.add(
            'discord',
            <LogInButton className="Button LogInButton--discord" icon="fab fa-discord" path="/auth/discord">
                {app.translator.trans('fof-auth-discord.forum.log_in.with_discord_button')}
            </LogInButton>
        );
    });
});
