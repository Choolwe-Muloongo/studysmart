<script>
(function () {
    const status = {
        supported: 'serviceWorker' in navigator,
        active: false,
        registrationAttempted: false
    };

    function emitStatus() {
        window.StudySmartSW = status;
        document.dispatchEvent(new CustomEvent('studysmart:sw-status', { detail: { ...status } }));
    }

    function updateOfflineSaveIndicators() {
        const buttons = document.querySelectorAll('.save-offline, [data-offline-save]');
        buttons.forEach((button) => {
            const existing = button.parentElement?.querySelector('.sw-unavailable-indicator');

            if (!status.active) {
                button.setAttribute('aria-disabled', 'true');
                button.classList.add('disabled');
                if (!existing) {
                    const note = document.createElement('div');
                    note.className = 'sw-unavailable-indicator small text-warning mt-1';
                    note.textContent = 'Offline save is unavailable. Reconnect, then refresh this page to enable it.';
                    button.parentElement?.appendChild(note);
                }
            } else {
                button.removeAttribute('aria-disabled');
                button.classList.remove('disabled');
                if (existing) existing.remove();
            }
        });
    }

    async function registerServiceWorker() {
        status.registrationAttempted = true;

        if (!status.supported) {
            emitStatus();
            updateOfflineSaveIndicators();
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            status.active = Boolean(registration?.active || navigator.serviceWorker.controller);
        } catch (_) {
            status.active = Boolean(navigator.serviceWorker.controller);
        }

        emitStatus();
        updateOfflineSaveIndicators();
    }

    document.addEventListener('DOMContentLoaded', updateOfflineSaveIndicators);
    window.addEventListener('load', registerServiceWorker);
})();
</script>
