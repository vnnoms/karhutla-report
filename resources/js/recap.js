const recapResults = document.querySelector('#recap-results');
const recapStatus = document.querySelector('#recap-live-status');

if (recapResults && recapStatus) {
    let activeRequest;

    const loadRecap = async (url, { updateHistory = true } = {}) => {
        activeRequest?.abort();
        const requestController = new AbortController();
        activeRequest = requestController;
        recapResults.setAttribute('aria-busy', 'true');
        recapStatus.textContent = 'Memperbarui rekap laporan…';

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error('Rekap laporan gagal dimuat.');
            }

            const documentFragment = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedResults = documentFragment.querySelector('.recap-results-fragment');

            if (!updatedResults) {
                window.location.assign(response.url);
                return;
            }

            recapResults.innerHTML = updatedResults.outerHTML;

            if (updateHistory) {
                window.history.pushState({}, '', url);
            }

            recapStatus.textContent = 'Rekap laporan berhasil diperbarui.';
        } catch (error) {
            if (error.name !== 'AbortError') {
                recapStatus.textContent = 'Rekap tidak dapat diperbarui. Coba lagi atau muat ulang halaman.';
            }
        } finally {
            if (activeRequest === requestController) {
                recapResults.setAttribute('aria-busy', 'false');
            }
        }
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.recap-filters');

        if (!form) {
            return;
        }

        event.preventDefault();
        const url = new URL(form.action, window.location.origin);
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            if (value !== '') {
                url.searchParams.set(key, value);
            }
        }

        loadRecap(url);
    });

    document.addEventListener('change', (event) => {
        if (event.target.matches('#province')) {
            const form = event.target.closest('.recap-filters');
            form.elements.city_regency.value = '';
            form.requestSubmit();
        }
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.recap-pagination a[href]');

        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        loadRecap(new URL(link.href));
    });

    window.addEventListener('popstate', () => {
        loadRecap(new URL(window.location.href), { updateHistory: false });
    });
}
