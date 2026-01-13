{{--
  Template Name: About Page
--}}

@extends('layouts.app')

@section('content')
<section class="about-page">
  <div class="robot-speaker-container">
    <div class="chat-container">
      <div class="chat-header">
        <h1>About Luke</h1>
        <a class="btn-primary" href="/" >Return Home</a>
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
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="120" height="120">
      <!-- Robot (upper body only, behind podium) -->
      <g id="bot-front">
        <!-- Antenna -->
        <rect x="11" y="2" width="2" height="2" fill="#6b7280"/>
        <rect x="11" y="2" width="2" height="1" fill="#ef4444"/>
        
        <!-- Head back -->
        <rect x="7" y="5" width="10" height="6" fill="#9ca3af"/>
        <rect x="8" y="6" width="8" height="4" fill="#d1d5db"/>
        
        <!-- Eyes -->
        <rect x="9" y="7" width="2" height="2" fill="#60a5fa"/>
        <rect x="13" y="7" width="2" height="2" fill="#60a5fa"/>
        
        <!-- Ears --> 
        <rect x="6" y="7" width="1" height="2" fill="#9ca3af"/> 
        <rect x="17" y="7" width="1" height="2" fill="#9ca3af"/>
        
        <!-- Neck -->
        <rect x="11" y="11" width="2" height="1" fill="#9ca3af"/>
        
        <!-- Body (partial, visible above podium) -->
        <rect x="8" y="12" width="8" height="2" fill="#9ca3af"/>
        <rect x="9" y="12" width="6" height="2" fill="#d1d5db"/>
        
        <!-- Arms resting on podium -->
        <rect x="6" y="13" width="2" height="2" fill="#9ca3af"/>
        <rect x="16" y="13" width="2" height="2" fill="#9ca3af"/>
      </g>
      
      <!-- Podium -->
      <g id="podium">
        <!-- Podium top surface -->
        <rect x="4" y="14" width="16" height="2" fill="#4b5563"/>
        
        <!-- Podium front panel -->
        <rect x="5" y="16" width="14" height="6" fill="#374151"/>
        
        <!-- Podium detail/emblem -->
        <rect x="10" y="18" width="4" height="2" fill="#6b7280"/>
        
        <!-- Podium base -->
        <rect x="6" y="22" width="12" height="2" fill="#4b5563"/>
      </g>
    </svg>
  </div>

  <div class="about-container">
    
  </div>
  
</section>
  
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