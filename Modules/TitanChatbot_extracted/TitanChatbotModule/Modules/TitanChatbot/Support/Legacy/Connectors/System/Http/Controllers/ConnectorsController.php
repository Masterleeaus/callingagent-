<?php

namespace Extensions\Connectors\System\Http\Controllers;

use Extensions\Connectors\System\Services\ConnectorStore;
use Extensions\Connectors\System\Services\ConnectorRunner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ConnectorsController extends Controller
{
    public function index(Request $request)
    {
        $providers = config('connectors.providers', []);
        $accounts = ConnectorStore::allAccounts();
        $templates = DB::table('connector_client_pack_templates')->orderByDesc('is_default')->orderBy('name')->get();

        return view('connectors::index', [
            'providers' => $providers,
            'accounts' => $accounts,
            'templates' => $templates,
        ]);
    }

    public function saveProvider(Request $request)
    {
        $provider = (string) $request->input('provider');
        if (!$provider) {
            return back()->with('error', 'Missing provider');
        }

        $payload = $request->except(['_token', 'provider']);
        ConnectorStore::saveAccount($provider, $payload);

        return back()->with('success', strtoupper($provider) . ' settings saved');
    }

    public function testProvider(Request $request)
    {
        $provider = (string) $request->input('provider');
        if (!$provider) {
            return response()->json(['ok' => false, 'message' => 'Missing provider'], 422);
        }

        $runner = new ConnectorRunner();
        $result = $runner->test($provider);

        return response()->json($result);
    }

    public function runClientPack(Request $request)
    {
        $runner = new ConnectorRunner();
        $result = $runner->runClientPack($request->all());
        return response()->json($result);
    }

    public function saveTemplate(Request $request)
    {
        $id = $request->input('id');
        $name = trim((string) $request->input('name'));
        $templateJson = (string) $request->input('template_json');
        $isDefault = (bool) $request->input('is_default');

        if ($name === '') {
            return back()->with('error', 'Template name is required');
        }

        $data = [
            'name' => $name,
            'template_json' => $templateJson,
            'is_default' => $isDefault,
            'updated_at' => now(),
        ];

        if ($id) {
            DB::table('connector_client_pack_templates')->where('id', $id)->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('connector_client_pack_templates')->insert($data);
        }

        if ($isDefault) {
            DB::table('connector_client_pack_templates')->where('name', '!=', $name)->update(['is_default' => false]);
        }

        return back()->with('success', 'Template saved');
    }

    public function deleteTemplate(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            DB::table('connector_client_pack_templates')->where('id', $id)->delete();
        }
        return back()->with('success', 'Template deleted');
    }

    public function setDefaultTemplate(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return back()->with('error', 'Missing template id');
        }
        DB::table('connector_client_pack_templates')->update(['is_default' => false]);
        DB::table('connector_client_pack_templates')->where('id', $id)->update(['is_default' => true]);
        return back()->with('success', 'Default template set');
    }

    // Webhook endpoint (no auth middleware) - called from service provider route group
    public function webhookClientPack(Request $request)
    {
        $secret = (string) ConnectorStore::getSetting('connectors_webhook_secret');
        $header = (string) $request->header('x-connectors-secret');

        if ($secret !== '' && !hash_equals($secret, $header)) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        $runner = new ConnectorRunner();
        $result = $runner->runClientPack($request->all());
        return response()->json($result);
    }
}
