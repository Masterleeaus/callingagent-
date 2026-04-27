<?php

return ['name'=>'CallingAgent','enabled'=>env('CALLING_AGENT_ENABLED', true),'default_provider'=>'twilio','default_transfer_number'=>env('CALLING_AGENT_DEFAULT_TRANSFER_NUMBER')];
