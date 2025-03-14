<div id="webhookPopupOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 998; display: flex; justify-content: center; align-items: center;">
    <div id="webhookPopup" style="width: 450px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); z-index: 999; padding: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #333; font-size: 1.5em;">Configuration du Webhook</h3>
            <button onclick="closePopup()" style="background: none; border: none; font-size: 1.5em; cursor: pointer; color: #666;">&times;</button>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label for="webhookInput" style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">URL du Webhook :</label>
            <input type="text" 
                   id="webhookInput" 
                   placeholder="https://exemple.com/webhook" 
                   style="width: 100%; 
                          padding: 12px; 
                          border: 1px solid #ddd; 
                          border-radius: 4px; 
                          font-size: 14px;
                          box-sizing: border-box;
                          margin-bottom: 15px;">
            <small style="color: #666; display: block; margin-top: -10px;">L'URL sera partiellement masquée après la validation</small>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button onclick="closePopup()" 
                    style="padding: 10px 20px; 
                           border: 1px solid #ddd; 
                           border-radius: 4px; 
                           background: white; 
                           color: #333;
                           cursor: pointer;
                           font-weight: 500;
                           transition: all 0.2s;">Annuler</button>
            <button onclick="submitWebhook()" 
                    style="padding: 10px 20px; 
                           border: none; 
                           border-radius: 4px; 
                           background: #2196F3; 
                           color: white;
                           cursor: pointer;
                           font-weight: 500;
                           transition: all 0.2s;">Valider</button>
        </div>
    </div>
</div>

<script>
    // Au chargement, vérifier s'il y a déjà un webhook
    document.addEventListener('DOMContentLoaded', function() {
        fetch('?action=getWebhook', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.webhook) {
                const input = document.getElementById('webhookInput');
                input.value = maskWebhook(data.webhook);
                input.style.border = '1px solid #00C851';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    });

    function submitWebhook() {
        const input = document.getElementById('webhookInput');
        const webhook = input.value.trim();
        
        if (!webhook) {
            input.style.border = '1px solid #ff4444';
            showError('Veuillez saisir une URL valide');
            return;
        }

        fetch('?action=saveWebhook', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ webhook: webhook }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const maskedWebhook = maskWebhook(data.webhook);
                input.value = maskedWebhook;
                input.style.border = '1px solid #00C851';
                
                // Changer le bouton de fermeture
                const closeBtn = document.querySelector('button[onclick="closePopup()"]');
                closeBtn.textContent = 'Continuer';
                closeBtn.style.background = '#00C851';
                closeBtn.style.color = 'white';
                closeBtn.style.border = 'none';
                closeBtn.onclick = function() {
                    window.location.href = window.location.pathname;
                };
            } else {
                input.style.border = '1px solid #ff4444';
                showError(data.error || 'Webhook invalide');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showError('Erreur lors de la sauvegarde du webhook');
        });
    }

    function maskWebhook(webhook) {
        try {
            const url = new URL(webhook);
            const domain = url.hostname;
            const path = url.pathname.replace(/\d/g, '*');
            return `${url.protocol}//${domain}${path}`;
        } catch (e) {
            return webhook;
        }
    }

    function closePopup() {
        document.getElementById('webhookPopupOverlay').style.display = 'none';
    }

    function showError(message) {
        const input = document.getElementById('webhookInput');
        const small = input.nextElementSibling;
        small.style.color = '#ff4444';
        small.textContent = message;
        setTimeout(() => {
            small.style.color = '#666';
            small.textContent = "L'URL sera partiellement masquée après la validation";
        }, 3000);
    }

    // Fermer avec la touche Echap
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePopup();
        }
    });

    // Empêcher la fermeture en cliquant sur la popup elle-même
    document.getElementById('webhookPopup').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Fermer en cliquant en dehors de la popup
    document.getElementById('webhookPopupOverlay').addEventListener('click', closePopup);
</script>
