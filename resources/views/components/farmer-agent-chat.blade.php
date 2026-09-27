@include('components.agent-chat._widget', [
    'chatRoute' => route('farmer.agent.chat'),
    'historyRoute' => route('farmer.agent.history'),
    'label' => 'Ask MarketLink',
])
