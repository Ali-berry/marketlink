@include('components.agent-chat._widget', [
    'chatRoute' => route('customer.agent.chat'),
    'historyRoute' => route('customer.agent.history'),
    'label' => 'Ask MarketLink',
])
