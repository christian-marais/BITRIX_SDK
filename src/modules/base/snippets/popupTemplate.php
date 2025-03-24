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
                   placeholder="<?= $webhookUrl ??'https://exemple.com/webhook' ?>" 
                   style="width: 100%; 
                          padding: 12px; 
                          border: 1px solid #ddd; 
                          border-radius: 4px; 
                          font-size: 14px;
                          box-sizing: border-box;
                          margin-bottom: 15px;"
                    value="">
            <small style="color: #666; display: block; margin-top: -10px;">L'URL doit être de la forme : https://domain/rest/uid/token/</small>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button onclick="submitWebhook('delete')" 
                    style="padding: 10px 20px; 
                           border: 1px solid #ddd; 
                           border-radius: 4px; 
                           background: red; 
                           color: white;
                           cursor: pointer;
                           font-weight: 500;
                           transition: all 0.2s;">Supprimer</button>
            <button onclick="submitWebhook('save')" 
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
        fetch('<?=BASE_URL?>api/webhook', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.webhook) {
                const input = document.getElementById('webhookInput');
                input.value = data.webhook;
                input.style.border = '1px solid #00C851';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    });

    function submitWebhook(action) {
        const input = document.getElementById('webhookInput');
        const webhook = input.value.trim();
        
        if (!webhook && action !== 'delete') {
            input.style.border = '1px solid #ff4444';
            showMessage('Veuillez saisir une URL valide', '#ff4444',action);
            return;
        }
        console.log(webhook,'<?=BASE_URL?>api/webhook/save');
        console.log(JSON.stringify({data: { webhook: webhook }}));
        fetch('<?=BASE_URL?>api/webhook/'+action, {
            method: (action=='save')?'POST':(action=='delete')?'DELETE':'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({data: { webhook: webhook }}),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status == 'success') {
                input.style.border = '1px solid #00C851';
                showMessage(data.message || 'Webhook ' + action + ' avec succès', '#00C851');
                // Changer le bouton de fermeture
                const closeBtn = document.querySelector('button[onclick="closePopup()"]');
                setTimeout(() => { window.location.href = window.location.pathname; }, 5000);
            } else {
                input.style.border = '1px solid #ff4444';
                showMessage(data.message || 'Webhook invalide', '#ff4444');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showMessage('Erreur '+error, '#ff4444');
        });
    }

    function closePopup() {
        document.getElementById('webhookPopupOverlay').style.display = 'none';
    }
    

    function showMessage(message,color,action=null) {
        const input = document.getElementById('webhookInput');
        const small = input.nextElementSibling;
        small.style.color = color;
        small.textContent = message;
        if(action==='save'){
            setTimeout(() => {
                small.style.color = color;
                small.textContent = "L'URL sera partiellement masquée après la validation";
            }, 2000);
        }
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
