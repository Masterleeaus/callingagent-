<?php
namespace Modules\TitanChatbot\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\TitanChatbot\Services\TitanChatbotModuleService;

class TitanChatbotController extends Controller
{
    public function __construct(private readonly TitanChatbotModuleService $module) {}

    public function index()
    {
        return response()->json($this->module->summary());
    }

    public function channels()
    {
        return response()->json($this->module->channels());
    }

    public function voice()
    {
        return response()->json($this->module->voice());
    }

    public function agent()
    {
        return response()->json($this->module->agent());
    }

    public function health()
    {
        return response()->json(['ok' => true, 'module' => 'TitanChatbot']);
    }
}
