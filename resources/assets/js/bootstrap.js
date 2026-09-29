
window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    //window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
} catch (e) { }

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set baseURL for Axios to handle subdirectory hosting
// Detect if running in a subdirectory (e.g. XAMPP: /sistema/public)
// When using artisan serve (127.0.0.1:8000), origin is the root so no prefix needed.
(function () {
    const pathname = window.location.pathname;
    // Find the index of '/public' in the path (XAMPP subdirectory hosting)
    const publicIndex = pathname.indexOf('/public');
    let projectPath = '';
    if (publicIndex !== -1) {
        projectPath = pathname.substring(0, publicIndex + '/public'.length);
    } else if (pathname.includes('/index.php')) {
        projectPath = pathname.split('/index.php')[0];
    }
    if (projectPath && projectPath !== '/') {
        window.axios.defaults.baseURL = projectPath;
    }
})();

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Axios Response Interceptor to automatically format HTTP 422 validation errors
 */
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error && error.response && error.response.status === 422) {
            const data = error.response.data;
            let formattedMsg = '';

            if (data && data.errors && typeof data.errors === 'object') {
                const messages = [];
                for (let field in data.errors) {
                    if (Array.isArray(data.errors[field])) {
                        messages.push(...data.errors[field]);
                    } else if (typeof data.errors[field] === 'string') {
                        messages.push(data.errors[field]);
                    }
                }
                if (messages.length > 0) {
                    formattedMsg = messages.join('\n');
                }
            }

            if (!formattedMsg && data && data.message) {
                formattedMsg = data.message;
            }

            if (formattedMsg && error.response && error.response.data) {
                error.response.data.message = formattedMsg;
            }
        }
        return Promise.reject(error);
    }
);

/**
 * Global Compatibility Wrapper for SweetAlert2 (v10+)
 * Converts legacy swal('title', 'msg', 'type') or swal({...}) calls to Swal.fire(...)
 * avoiding "TypeError: Cannot call a class as a function".
 */
(function () {
    let currentSwal = window.Swal || window.swal;

    function createSwalWrapper(target) {
        if (!target) return target;

        function swalWrapper(...args) {
            const actualSwal = window.Swal || target;
            if (actualSwal && typeof actualSwal.fire === 'function') {
                if (args.length === 1 && typeof args[0] === 'object') {
                    return actualSwal.fire(args[0]);
                }
                if (args.length >= 1) {
                    return actualSwal.fire(args[0], args[1], args[2]);
                }
            }
            if (typeof actualSwal === 'function') {
                try {
                    return new actualSwal(...args);
                } catch (e) {
                    if (actualSwal && actualSwal.fire) return actualSwal.fire(...args);
                }
            }
        }

        try {
            for (let prop in target) {
                if (Object.prototype.hasOwnProperty.call(target, prop)) {
                    swalWrapper[prop] = target[prop];
                }
            }
        } catch (e) {}

        if (target.fire) swalWrapper.fire = target.fire.bind(target);
        if (target.mixin) swalWrapper.mixin = target.mixin.bind(target);
        if (target.close) swalWrapper.close = target.close.bind(target);
        if (target.isVisible) swalWrapper.isVisible = target.isVisible.bind(target);
        if (target.DismissReason) swalWrapper.DismissReason = target.DismissReason;

        return swalWrapper;
    }

    try {
        Object.defineProperty(window, 'swal', {
            get: function () {
                const target = window.Swal || currentSwal;
                return createSwalWrapper(target);
            },
            set: function (val) {
                currentSwal = val;
            },
            configurable: true,
            enumerable: true
        });
    } catch (e) {
        if (window.Swal) {
            window.swal = createSwalWrapper(window.Swal);
        }
    }
})();

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key',
//     cluster: 'mt1',
//     encrypted: true
// });

