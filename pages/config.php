<?php
# ServerAuth plugin - a MantisBT plugin for delegating auth to the web server.
#
# Copyright (C) 2017 The Maker - make-all@users.github.com
# Licensed under the MIT license

form_security_validate("plugin_SteamAuth_config");
access_ensure_global_level(config_get("manage_plugin_threshold"));

/* Avoid touching timestamp if no change. */
function maybe_set_option($name, $value) {
	if ($value != plugin_config_get($name)) {
		plugin_config_set($name, $value);
	}
}

maybe_set_option("steamAPIKeyId", gpc_get_string("steamAPIKeyId", OFF));
maybe_set_option("domainName", gpc_get_string("domainName", OFF));

form_security_purge("plugin_SteamAuth_config");
print_successful_redirect(plugin_page("config_page", true));
