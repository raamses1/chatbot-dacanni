// ── CONFIGURACIÓN ─────────────────────────────────────────────
const DAC_API = 'http://localhost:8000/api/v1';
const DAC_LOGO = 'data:image/png;base64,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';
// ──────────────────────────────────────────────────────────────

let dacSession = localStorage.getItem('dac_session') || null;
let dacIsOpen  = false;

// ── INICIALIZAR ────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  buildWidget();
  setTimeout(() => {
    if (!dacIsOpen) document.getElementById('dac-notif').style.display = 'block';
  }, 3000);
});

function buildWidget() {
  document.body.insertAdjacentHTML('beforeend', `
    <div id="dac-bubble" onclick="dacToggle()">
      <img src="${DAC_LOGO}" alt="Dacanni">
      <div class="dac-notif" id="dac-notif"></div>
    </div>

    <div id="dac-chat">
      <div class="dac-header">
        <img class="dac-header-avatar" src="${DAC_LOGO}" alt="Dacanni">
        <div class="dac-header-info">
          <p class="dac-header-name">Dacanni</p>
          <p class="dac-header-sub">En línea</p>
        </div>
        <button class="dac-close" onclick="dacToggle()">✕</button>
      </div>
      <div class="dac-messages" id="dac-messages"></div>
      <div class="dac-input-area">
        <input id="dac-input" type="text" placeholder="Escribe tu pregunta..." maxlength="500" onkeydown="if(event.key==='Enter') dacSend()">
        <button id="dac-send" onclick="dacSend()">
          <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
      </div>
    </div>
  `);
}

// ── TOGGLE ─────────────────────────────────────────────────────
async function dacToggle() {
  dacIsOpen = !dacIsOpen;
  document.getElementById('dac-chat').classList.toggle('dac-open', dacIsOpen);
  document.getElementById('dac-notif').style.display = 'none';

  if (dacIsOpen) {
    const messages = document.getElementById('dac-messages');

    // Solo cargar historial si no hay mensajes ya renderizados
    if (messages.childElementCount === 0) {
      if (dacSession) {
        await dacLoadHistory();
      } else {
        dacAddMessage('bot', '¡Hola! 👋 Soy el asistente de Dacanni. ¿En qué puedo ayudarte hoy?');
      }
    }

    setTimeout(() => document.getElementById('dac-input').focus(), 300);
  }
}

async function dacLoadHistory() {
  try {
    const res = await fetch(`${DAC_API}/history?session=${dacSession}`, {
      headers: { 'Accept': 'application/json' }
    });

    const data = await res.json();

    if (!data.chats || data.chats.length === 0) {
      dacAddMessage('bot', '¡Hola! 👋 Soy el asistente de Dacanni. ¿En qué puedo ayudarte hoy?');
      return;
    }

    // Renderizar historial
    data.chats.forEach(chat => {
      dacAddMessage('user', chat.message);
      dacAddMessage('bot', chat.reply, [], chat.id);

      // Si ya fue calificado, marcar los botones
      if (chat.rating !== null) {
        const allRatingBtns = document.querySelectorAll('.dac-rating-btn');
        const lastBtns = Array.from(allRatingBtns).slice(-2);
        if (lastBtns.length === 2) {
          lastBtns[0].disabled = true;
          lastBtns[1].disabled = true;
          if (chat.rating === 1) lastBtns[0].classList.add('selected-up');
          else lastBtns[1].classList.add('selected-down');
        }
      }
    });

  } catch (err) {
    dacAddMessage('bot', '¡Hola! 👋 Soy el asistente de Dacanni. ¿En qué puedo ayudarte hoy?');
  }
}

// ── ENVIAR MENSAJE ─────────────────────────────────────────────
async function dacSend() {
  const input = document.getElementById('dac-input');
  const text  = input.value.trim();
  if (!text) return;

  input.value = '';
  input.disabled = true;

  dacAddMessage('user', text);
  dacShowTyping();

  try {
    const res = await fetch(`${DAC_API}/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ message: text, session: dacSession })
    });

    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();

    if (data.session) {
      dacSession = data.session;
      localStorage.setItem('dac_session', dacSession);
    }

    dacRemoveTyping();
    dacAddMessage('bot', data.reply, data.products || [], data.chat_id || null);

  } catch (err) {
    dacRemoveTyping();
    dacAddMessage('bot', 'Hubo un problema de conexión. Intenta de nuevo 😕');
  } finally {
    input.disabled = false;
    input.focus();
  }
}

// ── SELECCIONAR PRODUCTO ───────────────────────────────────────
async function dacSelectProduct(productId, productName) {
  document.querySelectorAll('.dac-product-btn').forEach(b => b.disabled = true);

  dacAddMessage('user', productName);
  dacShowTyping();

  try {
    const res = await fetch(`${DAC_API}/select`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ product_id: productId, session: dacSession })
    });

    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();

    dacRemoveTyping();
    dacAddMessage('bot', data.reply, data.products || [], data.chat_id || null);

  } catch (err) {
    dacRemoveTyping();
    dacAddMessage('bot', 'Hubo un problema de conexión. Intenta de nuevo 😕');
  }
}

// ── AGREGAR MENSAJE ────────────────────────────────────────────
function dacAddMessage(role, text, products = [], chatId = null) {
  const container = document.getElementById('dac-messages');

  const wrapper = document.createElement('div');
  wrapper.className = `dac-msg ${role}`;

  const bubble = document.createElement('div');
bubble.className = 'dac-bubble-text';

// Convertir saltos de línea y links clickeables
const formatted = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\n/g, '<br>')
    .replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" style="color:#E8197D;word-break:break-all;">$1</a>');

bubble.innerHTML = formatted;
wrapper.appendChild(bubble);

  // Botones de productos
  if (role === 'bot' && products.length > 0) {
    const btns = document.createElement('div');
    btns.className = 'dac-products';

    products.forEach(p => {
      const btn = document.createElement('button');
      btn.className = 'dac-product-btn';

      const name = document.createElement('span');
      name.textContent = p.name;
      btn.appendChild(name);

      if (p.price) {
        const price = document.createElement('span');
        price.className = 'dac-price-tag';
        price.textContent = '$' + p.price;
        btn.appendChild(price);
      }

      btn.onclick = () => dacSelectProduct(p.id, p.name);
      btns.appendChild(btn);
    });

    wrapper.appendChild(btns);
  }

  // Botones de calificación (solo bot con chatId)
  if (role === 'bot' && chatId) {
    const rating = document.createElement('div');
    rating.className = 'dac-rating';

    const btnUp = document.createElement('button');
    btnUp.className = 'dac-rating-btn';
    btnUp.textContent = '👍';
    btnUp.onclick = () => dacRate(chatId, 1, btnUp, btnDown);

    const btnDown = document.createElement('button');
    btnDown.className = 'dac-rating-btn';
    btnDown.textContent = '👎';
    btnDown.onclick = () => dacRate(chatId, 0, btnUp, btnDown);

    rating.appendChild(btnUp);
    rating.appendChild(btnDown);
    wrapper.appendChild(rating);
  }

  container.appendChild(wrapper);
  container.scrollTop = container.scrollHeight;
}

async function dacRate(chatId, rating, btnUp, btnDown) {
  btnUp.disabled   = true;
  btnDown.disabled = true;

  if (rating === 1) btnUp.classList.add('selected-up');
  else btnDown.classList.add('selected-down');

  try {
    await fetch(`${DAC_API}/rate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ chat_id: chatId, rating: rating })
    });
  } catch (err) {
    console.error('Error al calificar:', err);
  }
}

// ── TYPING ─────────────────────────────────────────────────────
function dacShowTyping() {
  const container = document.getElementById('dac-messages');
  const el = document.createElement('div');
  el.className = 'dac-msg bot';
  el.id = 'dac-typing';
  el.innerHTML = '<div class="dac-typing-wrap"><span></span><span></span><span></span></div>';
  container.appendChild(el);
  container.scrollTop = container.scrollHeight;
}

function dacRemoveTyping() {
  const t = document.getElementById('dac-typing');
  if (t) t.remove();
}