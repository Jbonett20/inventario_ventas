/**
 * ============================================================
 * SISTEMA INTEGRAL DE GESTIÓN (SIG)
 * Módulo Offline-Sync - Trabajo sin conexión
 * ============================================================
 * 
 * Este módulo permite:
 * 1. Detectar cambios en la conectividad (online/offline)
 * 2. Almacenar datos localmente cuando no hay internet
 * 3. Sincronizar automáticamente cuando se restaura la conexión
 * 4. NO recarga ni bloquea el software
 * 
 * Tecnología: localStorage (fallback) + IndexedDB (principal)
 * Versión: 1.0 - 2026-07-23
 */

const SIG_OfflineSync = (function() {
    
    'use strict';

    // ============================================================
    // CONFIGURACIÓN
    // ============================================================
    const CONFIG = {
        dbName: 'SIG_OfflineDB',
        dbVersion: 1,
        storeName: 'sync_queue',
        pendingStore: 'pending_data',
        maxRetries: 3,
        retryInterval: 5000, // 5 segundos entre reintentos
        syncInterval: 30000,  // 30 segundos entre intentos de sincronización
        debug: false
    };

    // ============================================================
    // ESTADO INTERNO
    // ============================================================
    let _db = null;
    let _isOnline = navigator.onLine;
    let _syncTimer = null;
    let _isSyncing = false;
    let _callbacks = {
        onOnline: [],
        onOffline: [],
        onSyncStart: [],
        onSyncComplete: [],
        onSyncError: [],
        onSyncProgress: []
    };

    // ============================================================
    // INDEXEDDB - INICIALIZACIÓN
    // ============================================================
    function _initDB() {
        return new Promise((resolve, reject) => {
            if (_db) {
                resolve(_db);
                return;
            }

            const request = indexedDB.open(CONFIG.dbName, CONFIG.dbVersion);

            request.onerror = function(event) {
                _log('Error al abrir IndexedDB:', event.target.error);
                // Fallback a localStorage
                resolve(null);
            };

            request.onupgradeneeded = function(event) {
                const db = event.target.result;
                
                // Store para cola de sincronización
                if (!db.objectStoreNames.contains(CONFIG.storeName)) {
                    const store = db.createObjectStore(CONFIG.storeName, { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    store.createIndex('status', 'status', { unique: false });
                    store.createIndex('created_at', 'created_at', { unique: false });
                    store.createIndex('entity', 'entity', { unique: false });
                }

                // Store para datos pendientes locales
                if (!db.objectStoreNames.contains(CONFIG.pendingStore)) {
                    const pStore = db.createObjectStore(CONFIG.pendingStore, {
                        keyPath: 'key'
                    });
                }

                _log('Base de datos IndexedDB actualizada a versión', event.target.result.version);
            };

            request.onsuccess = function(event) {
                _db = event.target.result;
                _log('IndexedDB inicializada correctamente');
                
                _db.onversionchange = function() {
                    _db.close();
                    _log('Base de datos actualizada, cerrando conexión antigua');
                };

                resolve(_db);
            };
        });
    }

    // ============================================================
    // OPERACIONES CON INDEXEDDB
    // ============================================================
    function _dbAdd(storeName, data) {
        return new Promise((resolve, reject) => {
            if (!_db) {
                // Fallback a localStorage
                _localStorageAdd(storeName, data);
                resolve({ success: true, fallback: 'localStorage' });
                return;
            }

            const tx = _db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.add(data);

            request.onsuccess = function() {
                _log('Dato agregado a', storeName, 'con ID:', request.result);
                resolve({ success: true, id: request.result });
            };

            request.onerror = function(event) {
                _log('Error al agregar a', storeName, ':', event.target.error);
                // Fallback
                _localStorageAdd(storeName, data);
                resolve({ success: true, fallback: 'localStorage' });
            };
        });
    }

    function _dbPut(storeName, data) {
        return new Promise((resolve, reject) => {
            if (!_db) {
                _localStoragePut(storeName, data);
                resolve({ success: true, fallback: 'localStorage' });
                return;
            }

            const tx = _db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.put(data);

            request.onsuccess = function() {
                resolve({ success: true });
            };

            request.onerror = function() {
                _localStoragePut(storeName, data);
                resolve({ success: true, fallback: 'localStorage' });
            };
        });
    }

    function _dbGetAll(storeName) {
        return new Promise((resolve, reject) => {
            if (!_db) {
                resolve(_localStorageGetAll(storeName));
                return;
            }

            const tx = _db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = function() {
                resolve(request.result || []);
            };

            request.onerror = function() {
                resolve(_localStorageGetAll(storeName));
            };
        });
    }

    function _dbDelete(storeName, id) {
        return new Promise((resolve, reject) => {
            if (!_db) {
                _localStorageDelete(storeName, id);
                resolve(true);
                return;
            }

            const tx = _db.transaction(storeName, 'readwrite');
            const store = tx.objectStore(storeName);
            const request = store.delete(id);

            request.onsuccess = function() {
                resolve(true);
            };

            request.onerror = function() {
                _localStorageDelete(storeName, id);
                resolve(true);
            };
        });
    }

    function _dbGetByIndex(storeName, indexName, value) {
        return new Promise((resolve, reject) => {
            if (!_db) {
                resolve([]);
                return;
            }

            const tx = _db.transaction(storeName, 'readonly');
            const store = tx.objectStore(storeName);
            const index = store.index(indexName);
            const request = index.getAll(value);

            request.onsuccess = function() {
                resolve(request.result || []);
            };

            request.onerror = function() {
                resolve([]);
            };
        });
    }

    // ============================================================
    // FALLBACK: LOCALSTORAGE
    // ============================================================
    function _getLSKey(storeName) {
        return 'SIG_' + storeName;
    }

    function _localStorageAdd(storeName, data) {
        const key = _getLSKey(storeName);
        let items = [];
        try {
            const stored = localStorage.getItem(key);
            items = stored ? JSON.parse(stored) : [];
        } catch(e) {
            items = [];
        }
        data.id = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        items.push(data);
        localStorage.setItem(key, JSON.stringify(items));
    }

    function _localStoragePut(storeName, data) {
        const key = _getLSKey(storeName);
        try {
            const stored = localStorage.getItem(key);
            let items = stored ? JSON.parse(stored) : [];
            const idx = items.findIndex(item => item.id === data.id);
            if (idx >= 0) {
                items[idx] = data;
            } else {
                items.push(data);
            }
            localStorage.setItem(key, JSON.stringify(items));
        } catch(e) {
            _log('Error en localStorage put:', e);
        }
    }

    function _localStorageGetAll(storeName) {
        const key = _getLSKey(storeName);
        try {
            const stored = localStorage.getItem(key);
            return stored ? JSON.parse(stored) : [];
        } catch(e) {
            return [];
        }
    }

    function _localStorageDelete(storeName, id) {
        const key = _getLSKey(storeName);
        try {
            const stored = localStorage.getItem(key);
            let items = stored ? JSON.parse(stored) : [];
            items = items.filter(item => item.id !== id);
            localStorage.setItem(key, JSON.stringify(items));
        } catch(e) {}
    }

    // ============================================================
    // DETECCIÓN DE CONECTIVIDAD
    // ============================================================
    function _setupListeners() {
        window.addEventListener('online', function() {
            _log('🌐 Conexión restaurada');
            _isOnline = true;
            _notifyCallbacks('onOnline');
            _startSync();
        });

        window.addEventListener('offline', function() {
            _log('📡 Conexión perdida - modo offline activado');
            _isOnline = false;
            _notifyCallbacks('onOffline');
            _stopSync();
        });
    }

    // ============================================================
    // NOTIFICACIONES A CALLBACKS
    // ============================================================
    function _notifyCallbacks(event, data) {
        if (_callbacks[event]) {
            _callbacks[event].forEach(function(cb) {
                try {
                    cb(data);
                } catch(e) {
                    _log('Error en callback', event, ':', e);
                }
            });
        }
    }

    // ============================================================
    // LOGGING
    // ============================================================
    function _log() {
        if (CONFIG.debug) {
            console.log('[SIG-Offline]', ...arguments);
        }
    }

    // ============================================================
    // API PÚBLICA
    // ============================================================

    /**
     * Inicializar el módulo offline
     */
    async function init(options) {
        if (options) {
            if (options.debug) CONFIG.debug = options.debug;
            if (options.retryInterval) CONFIG.retryInterval = options.retryInterval;
            if (options.syncInterval) CONFIG.syncInterval = options.syncInterval;
        }

        _log('Inicializando módulo offline...');
        await _initDB();
        _setupListeners();

        // Sincronizar pendientes al iniciar si hay conexión
        if (_isOnline) {
            setTimeout(_startSync, 2000);
        }

        _log('✅ Módulo offline inicializado. Estado:', _isOnline ? 'ONLINE' : 'OFFLINE');
        return { initialized: true, online: _isOnline };
    }

    /**
     * Verificar si hay conexión a internet
     */
    function isOnline() {
        return _isOnline;
    }

    /**
     * Agregar una operación a la cola de sincronización
     * @param {string} entity - Nombre de la entidad (ej: 'factura', 'producto')
     * @param {string} action - Acción (ej: 'create', 'update', 'delete')
     * @param {object} data - Datos de la operación
     * @param {string} endpoint - URL del endpoint API
     */
    async function enqueue(entity, action, data, endpoint) {
        const item = {
            entity: entity,
            action: action,
            data: data,
            endpoint: endpoint || null,
            status: 'pending',
            retries: 0,
            created_at: new Date().toISOString(),
            last_attempt: null
        };

        await _dbAdd(CONFIG.storeName, item);
        _log('📝 Operación encolada:', entity, action);

        // Si hay conexión, intentar sincronizar inmediatamente
        if (_isOnline) {
            _processQueue();
        }

        return { queued: true };
    }

    /**
     * Guardar datos localmente para uso offline
     * @param {string} key - Clave del dato
     * @param {any} value - Valor a almacenar
     */
    async function saveLocal(key, value) {
        await _dbPut(CONFIG.pendingStore, {
            key: key,
            value: value,
            updated_at: new Date().toISOString()
        });
        _log('💾 Dato guardado localmente:', key);
    }

    /**
     * Obtener datos almacenados localmente
     * @param {string} key - Clave del dato
     */
    async function getLocal(key) {
        if (!_db) {
            const all = _localStorageGetAll(CONFIG.pendingStore);
            const item = all.find(i => i.key === key);
            return item ? item.value : null;
        }

        const tx = _db.transaction(CONFIG.pendingStore, 'readonly');
        const store = tx.objectStore(CONFIG.pendingStore);
        const request = store.get(key);

        return new Promise((resolve) => {
            request.onsuccess = function() {
                resolve(request.result ? request.result.value : null);
            };
            request.onerror = function() {
                resolve(null);
            };
        });
    }

    /**
     * Obtener todos los datos locales
     */
    async function getAllLocal() {
        return await _dbGetAll(CONFIG.pendingStore);
    }

    /**
     * Obtener la cola de sincronización pendiente
     */
    async function getQueue() {
        return await _dbGetByIndex(CONFIG.storeName, 'status', 'pending');
    }

    /**
     * Obtener estadísticas de la cola
     */
    async function getStats() {
        const pending = await _dbGetByIndex(CONFIG.storeName, 'status', 'pending');
        const all = await _dbGetAll(CONFIG.storeName);
        
        return {
            total: all.length,
            pending: pending.length,
            synced: all.length - pending.length,
            isOnline: _isOnline,
            isSyncing: _isSyncing,
            storageUsed: JSON.stringify(localStorage).length + ' bytes'
        };
    }

    // ============================================================
    // PROCESAMIENTO DE COLA
    // ============================================================
    async function _processQueue() {
        if (_isSyncing) return;
        _isSyncing = true;
        _notifyCallbacks('onSyncStart');

        try {
            const pendingItems = await _dbGetByIndex(CONFIG.storeName, 'status', 'pending');
            
            if (pendingItems.length === 0) {
                _isSyncing = false;
                _notifyCallbacks('onSyncComplete', { synced: 0 });
                return;
            }

            _log('🔄 Procesando', pendingItems.length, 'operaciones pendientes...');
            _notifyCallbacks('onSyncProgress', { 
                total: pendingItems.length, 
                processed: 0 
            });

            let processed = 0;
            let errors = 0;

            for (const item of pendingItems) {
                try {
                    await _syncItem(item);
                    await _dbDelete(CONFIG.storeName, item.id);
                    processed++;
                    _log('✅ Sincronizado:', item.entity, item.action);
                    
                    _notifyCallbacks('onSyncProgress', {
                        total: pendingItems.length,
                        processed: processed,
                        current: item
                    });
                } catch (error) {
                    errors++;
                    item.retries++;
                    item.last_attempt = new Date().toISOString();
                    
                    if (item.retries >= CONFIG.maxRetries) {
                        item.status = 'failed';
                        _log('❌ Falló después de', CONFIG.maxRetries, 'intentos:', item.entity);
                    } else {
                        item.status = 'pending';
                        _log('⚠️ Error al sincronizar (intento', item.retries, 'de', CONFIG.maxRetries, '):', error.message);
                    }
                    
                    await _dbPut(CONFIG.storeName, item);
                }
            }

            _isSyncing = false;
            _notifyCallbacks('onSyncComplete', { 
                synced: processed, 
                errors: errors 
            });

            if (errors > 0) {
                _notifyCallbacks('onSyncError', { 
                    message: errors + ' operaciones fallaron',
                    errors: errors
                });
            }

        } catch (error) {
            _isSyncing = false;
            _log('Error procesando cola:', error);
            _notifyCallbacks('onSyncError', { message: error.message });
        }
    }

    async function _syncItem(item) {
        const endpoint = item.endpoint || _getEndpoint(item.entity, item.action);
        
        if (!endpoint) {
            throw new Error('No hay endpoint para ' + item.entity + '/' + item.action);
        }

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-SIG-Offline-Sync': 'true'
            },
            body: JSON.stringify({
                _offline_action: item.action,
                _offline_entity: item.entity,
                _offline_id: item.id,
                ...item.data
            })
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error('HTTP ' + response.status + ': ' + text);
        }

        return await response.json();
    }

    function _getEndpoint(entity, action) {
        const endpoints = {
            'factura_create':     'app/api/facturas/guardar.php',
            'factura_update':     'app/api/facturas/actualizar.php',
            'producto_create':    'app/api/productos/guardar.php',
            'producto_update':    'app/api/productos/actualizar.php',
            'cliente_create':     'app/api/clientes/guardar.php',
            'cliente_update':     'app/api/clientes/actualizar.php',
            'ingreso_create':     'app/api/ingresos/guardar.php',
            'inventario_adjust':  'app/api/inventario/ajustar.php'
        };

        return endpoints[entity + '_' + action] || null;
    }

    // ============================================================
    // CONTROL DE SINCRONIZACIÓN
    // ============================================================
    function _startSync() {
        _stopSync();
        _processQueue();
        _syncTimer = setInterval(_processQueue, CONFIG.syncInterval);
    }

    function _stopSync() {
        if (_syncTimer) {
            clearInterval(_syncTimer);
            _syncTimer = null;
        }
    }

    // ============================================================
    // REGISTRO DE CALLBACKS
    // ============================================================
    function on(event, callback) {
        if (_callbacks[event]) {
            _callbacks[event].push(callback);
        }
        return this;
    }

    function off(event, callback) {
        if (_callbacks[event]) {
            _callbacks[event] = _callbacks[event].filter(function(cb) {
                return cb !== callback;
            });
        }
        return this;
    }

    // ============================================================
    // EXPORTAR API
    // ============================================================
    return {
        init: init,
        isOnline: isOnline,
        enqueue: enqueue,
        saveLocal: saveLocal,
        getLocal: getLocal,
        getAllLocal: getAllLocal,
        getQueue: getQueue,
        getStats: getStats,
        processQueue: _processQueue,
        on: on,
        off: off
    };

})();
