{{--
  Template Name: About Page
--}}

@extends('layouts.app')

@section('content')
  <div class="chat-container">
    <div class="chat-header">
      <h1>Chat with Luke's Robot</h1>
      <p>Ask me anything about Luke's work, skills, or background!</p>
    </div>
    
    <div class="chat-messages" id="chatMessages">
      <div class="message bot">
        <div class="message-content">
          Hey there! I'm Luke's robot speaking on his behalf. What would you like to know about Luke?
        </div>
      </div>
    </div>
    
    <div class="chat-input-container">
      <input 
        type="text" 
        id="chatInput" 
        placeholder="Type your message..." 
        autocomplete="off"
      />
      <button id="chatSend" type="button">Send</button>
    </div>
  </div>
@endsection

@push('scripts')
<script>
(function() {
  const messages = document.getElementById('chatMessages');
  const input = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');
  const API_URL = '{{ home_url('/wp-json/luke/v1/chat') }}';

  function addMessage(text, isBot = false) {
    const div = document.createElement('div');
    div.className = 'message ' + (isBot ? 'bot' : 'user');
    div.innerHTML = '<div class="message-content">' + text + '</div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function addTypingIndicator() {
    const div = document.createElement('div');
    div.className = 'message bot typing';
    div.id = 'typingIndicator';
    div.innerHTML = '<div class="message-content">...</div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function removeTypingIndicator() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
  }

  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;

    addMessage(text, false);
    input.value = '';
    input.disabled = true;
    sendBtn.disabled = true;
    addTypingIndicator();

    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });

      const data = await response.json();
      removeTypingIndicator();

      if (data.reply) {
        addMessage(data.reply, true);
      } else if (data.message) {
        addMessage('Sorry, something went wrong: ' + data.message, true);
      }
    } catch (err) {
      removeTypingIndicator();
      addMessage('Sorry, I had trouble connecting. Please try again.', true);
    }

    input.disabled = false;
    sendBtn.disabled = false;
    input.focus();
  }

  sendBtn.addEventListener('click', sendMessage);
  input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });
})();
</script>
@endpush