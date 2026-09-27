@include('components.agent-chat._widget', [
    'chatRoute' => route('admin.agent.chat'),
    'historyRoute' => route('admin.agent.history'),
    'label' => 'Ask MarketLink',
])
