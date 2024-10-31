<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI;

use Setka\Editor\Admin\Service\APIs\ClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SetkaEditorAPIFactory
{
    public static function create(
        ValidatorInterface $validator,
        string $wpVersion,
        string $pluginVersion,
        ClientInterface $client,
        string $endpoint = Endpoints::API,
        ?string $basicAuthLogin = null,
        ?string $basicAuthPassword = null
    ): SetkaEditorAPI {
        $options = array(
            'app_version' => $wpVersion,
            'plugin_version' => $pluginVersion,
            'domain' => get_site_url(),
            'endpoint' => $endpoint,
            'basic_auth_login' => $basicAuthLogin ?? false,
            'basic_auth_password' => $basicAuthPassword ?? false,
        );

        return new SetkaEditorAPI($client, $validator, $options);
    }
}
