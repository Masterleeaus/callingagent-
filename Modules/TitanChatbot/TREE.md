# TitanChatbot — Directory Tree

Current structure of the TitanChatbot module including all AI-native directories.

```
TitanChatbot/
├── ACCEPTANCE.md
├── CHANGELOG.md
├── DIRECTORY_TREE.md
├── LICENSE
├── README.md
├── TREE.md
├── module.json
├── module.lock.json
├── phpunit.xml
├── version.json
│
├── AI/
│   ├── README.md
│   ├── Actions/
│   │   └── action-map.json
│   ├── Agents/
│   │   ├── BookingAgent.php
│   │   ├── CleaningBusinessAgent.php
│   │   ├── ConversationAgent.php
│   │   ├── ReceptionistAgent.php
│   │   ├── SupportAgent.php
│   │   └── VoiceAgent.php
│   ├── Attributes/
│   │   ├── Desc.php
│   │   └── Tool.php
│   ├── Citations/
│   │   └── citation.schema.json
│   ├── Core/
│   │   └── TitanAgent.php
│   ├── DataModels/
│   │   ├── BaseDataModel.php
│   │   ├── BookingIntentModel.php
│   │   ├── DataModelInterface.php
│   │   ├── EscalationDecisionModel.php
│   │   ├── QuoteRequestModel.php
│   │   ├── TrainingResultModel.php
│   │   └── VoiceResponseModel.php
│   ├── Guardrails/
│   │   └── guardrails.json
│   ├── Indexing/
│   │   └── indexing.manifest.json
│   ├── Memory/
│   │   ├── ConversationMemory.php
│   │   ├── ConversationMemoryStore.php
│   │   ├── MemoryDriverInterface.php
│   │   ├── StorageManager.php
│   │   ├── Drivers/
│   │   │   ├── CacheMemoryDriver.php
│   │   │   ├── DatabaseMemoryDriver.php
│   │   │   ├── FileMemoryDriver.php
│   │   │   └── InMemoryMemoryDriver.php
│   │   └── Truncation/
│   │       ├── SimpleTruncationStrategy.php
│   │       ├── SummarizationStrategy.php
│   │       └── TruncationStrategyInterface.php
│   ├── Pipelines/
│   │   ├── RagAnswerPipeline.php
│   │   ├── RagPipeline.php
│   │   └── VoiceConversationPipeline.php
│   ├── Prompts/
│   │   └── cleaning_receptionist.php
│   ├── Providers/
│   │   └── FakeGeneratorProvider.php
│   ├── Retrieval/
│   │   └── retrieval.policy.json
│   ├── Telemetry/
│   │   └── telemetry.manifest.json
│   └── Tools/
│       ├── EmbeddingResolver.php
│       ├── QuoteEstimatorTool.php
│       ├── QuoteTool.php
│       ├── SchemaGenerator.php
│       └── ToolRegistry.php
│
├── Agents/
│   └── BookingAgent/
│       ├── agent.manifest.json
│       ├── evaluations/
│       │   └── eval-suite.json
│       ├── memory/
│       │   └── .gitkeep
│       ├── policies/
│       │   └── agent-policy.md
│       ├── prompts/
│       │   ├── answering.md
│       │   ├── system.md
│       │   └── tool-use.md
│       ├── tools/
│       │   ├── calculate-quote.tool.json
│       │   ├── check-service-area.tool.json
│       │   ├── create-booking-request.tool.json
│       │   ├── create-escalation.tool.json
│       │   ├── lookup-available-slots.tool.json
│       │   ├── lookup-customer.tool.json
│       │   └── search-knowledge.tool.json
│       └── training/
│           └── dataset-manifest.json
│
├── Knowledge/
│   ├── README.md
│   ├── checklists/
│   │   └── quality-checklist.md
│   ├── compliance/
│   │   └── compliance-notes.md
│   ├── contracts/
│   │   └── sample-service-agreement.md
│   ├── examples/
│   │   └── sample-booking-conversation.md
│   ├── faqs/
│   │   └── cleaning-faqs.md
│   ├── guides/
│   │   └── cleaning-estimating-guide.md
│   ├── policies/
│   │   ├── service-policy.md
│   │   └── source-approval-policy.md
│   ├── pricing/
│   │   └── rate-card.md
│   └── sops/
│       └── operator-sop.md
│
├── Docs/
│   ├── AI_NATIVE_MODULE_GUIDE.md
│   └── BLUEPRINT_NOTES.md
│
├── API/
│   ├── Contracts/
│   │   └── ApiResponseContract.php
│   ├── Serializers/
│   │   └── ApiSerializer.php
│   └── Transformers/
│       ├── ChatbotTransformer.php
│       └── ConversationTransformer.php
│
├── Actions/
│   ├── GenerateChatbotReply.php
│   └── RouteInboundMessage.php
│
├── Automation/
│   ├── Handlers/
│   ├── Pipelines/
│   ├── Schedulers/
│   └── Triggers/
│
├── Billing/
│   ├── Limits/
│   ├── Meters/
│   ├── Plans/
│   └── Usage/
│
├── Console/
│   └── Commands/
│       └── AuditTitanChatbotCommand.php
│
├── Tests/
│   ├── bootstrap.php
│   ├── Feature/
│   │   └── TitanChatbotBuilderTest.php
│   ├── Integration/
│   │   └── TitanChatbotChannelTest.php
│   └── Unit/
│       ├── AiNativeStructureTest.php
│       ├── BillingMeterTest.php
│       ├── ChannelRouterTest.php
│       ├── DataModelTest.php
│       ├── MessagePayloadTest.php
│       ├── SchemaGeneratorTest.php
│       ├── StorageManagerTest.php
│       ├── TitanAgentTest.php
│       ├── TitanChatbotModuleServiceTest.php
│       ├── TitanChatbotStructureTest.php
│       ├── TrainingPipelineTest.php
│       ├── TruncationStrategyTest.php
│       └── UsageRecordTest.php
│
└── manifests/
    ├── ai.manifest.json
    ├── ai_tools.json
    ├── api.manifest.json
    └── ... (other manifests)
```
