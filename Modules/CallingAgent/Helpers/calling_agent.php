<?php
if(!function_exists('calling_agent_enabled')){function calling_agent_enabled(): bool { return (bool) config('calling-agent.config.enabled', true); }}
