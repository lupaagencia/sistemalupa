<template>
  <div class="web-ide-wrapper">
    <div class="web-ide-container">
      <!-- Top Navigation Header -->
      <div class="ide-header">
        <div class="header-left">
          <div class="brand-badge">
            <span class="brand-icon">⚡</span>
            <span class="brand-title">ANTIGRAVITY WEB STUDIO</span>
          </div>

          <div class="active-file-indicator" v-if="activeFilePath">
            <i class="fa fa-code file-icon"></i>
            <span class="file-path">{{ activeFilePath }}</span>
            <span v-if="isModified" class="dot-modified" title="Cambios sin guardar"></span>
          </div>
          <div class="active-file-indicator empty" v-else>
            <i class="fa fa-folder-open-o"></i>
            <span>Selecciona un archivo para editar</span>
          </div>

          <transition name="fade">
            <div v-if="syntaxStatus" :class="['status-pill', syntaxStatus.type]">
              <i class="fa" :class="syntaxStatus.type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle'"></i>
              <span>{{ syntaxStatus.message }}</span>
            </div>
          </transition>
        </div>

        <div class="header-right">
          <button class="btn-ide btn-save" :disabled="saving || !activeFilePath" @click="saveActiveFile">
            <i class="fa" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i>
            <span>{{ saving ? 'Guardando...' : 'Guardar (Ctrl+S)' }}</span>
          </button>
          
          <button class="btn-ide btn-deploy" :disabled="deploying" @click="deployFtp">
            <i class="fa" :class="deploying ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i>
            <span>{{ deploying ? 'Desplegando...' : 'Desplegar FTP' }}</span>
          </button>

          <button class="btn-ide btn-icon" @click="reloadTree" title="Actualizar Explorador">
            <i class="fa fa-refresh" :class="{'fa-spin': loadingTree}"></i>
          </button>
        </div>
      </div>

      <!-- Main Workspace -->
      <div class="ide-workspace">
        
        <!-- File Explorer Sidebar -->
        <div class="explorer-sidebar">
          <div class="explorer-title">
            <span>EXPLORADOR DE ARCHIVOS</span>
            <span class="file-count" v-if="filteredTree.length">{{ filteredTree.length }} ítems</span>
          </div>

          <div class="search-box">
            <i class="fa fa-search search-icon"></i>
            <input type="text" v-model="fileSearch" placeholder="Buscar archivo o carpeta..." />
            <i v-if="fileSearch" class="fa fa-times clear-search" @click="fileSearch = ''"></i>
          </div>

          <div class="tree-viewport">
            <div v-if="loadingTree" class="loading-state">
              <div class="spinner-neon"></div>
              <span>Cargando directorio del proyecto...</span>
            </div>
            <div v-else-if="filteredTree.length === 0" class="empty-state">
              <i class="fa fa-search-minus"></i>
              <span>No se encontraron archivos</span>
            </div>
            <div v-else class="tree-list">
              <tree-item v-for="node in filteredTree" :key="node.path" :item="node" :active-path="activeFilePath" @open-file="openFile"></tree-item>
            </div>
          </div>
        </div>

        <!-- Editor Center -->
        <div class="editor-viewport">
          <div v-show="loadingFile" class="editor-loading-overlay">
            <div class="spinner-neon"></div>
            <span>Abriendo {{ activeFilePath }}...</span>
          </div>
          
          <div v-if="!activeFilePath" class="welcome-screen">
            <div class="welcome-card">
              <div class="welcome-logo">⚡</div>
              <h2>Bienvenido a Antigravity Web Studio</h2>
              <p>Selecciona un archivo del explorador lateral para editar su código en tiempo real.</p>
              <div class="shortcuts">
                <span class="shortcut"><code>Ctrl + S</code> Guardar y verificar sintaxis</span>
                <span class="shortcut"><code>Auto Sync</code> Sincronización automática con GitHub</span>
              </div>
            </div>
          </div>

          <div id="monaco-editor-canvas" class="monaco-canvas" :style="{ display: activeFilePath ? 'block' : 'none' }"></div>
        </div>

        <!-- AI Agent Side Panel -->
        <div class="ai-sidebar">
          <div class="ai-header">
            <div class="agent-info">
              <div class="agent-avatar">⚡</div>
              <div>
                <div class="agent-name">Antigravity IA Agent</div>
                <div class="agent-status"><span class="pulse-dot"></span> Pair Programmer Activo</div>
              </div>
            </div>
            <button class="btn-icon-subtle" @click="clearChat" title="Limpiar chat">
              <i class="fa fa-trash-o"></i>
            </button>
          </div>

          <!-- Messages Stream -->
          <div class="chat-viewport" ref="chatHistoryRef">
            <div v-for="(msg, idx) in chatMessages" :key="idx" :class="['chat-bubble', msg.role]">
              <div class="bubble-header">
                <span class="sender">{{ msg.role === 'user' ? 'Superadministrador' : '⚡ Antigravity Agent' }}</span>
                <span class="time">{{ msg.time }}</span>
              </div>
              <div class="bubble-content" v-html="formatMessageText(msg.text)"></div>
              <div v-if="msg.applied" class="applied-badge mt-2 p-2 rounded text-emerald font-weight-bold" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); font-size: 0.78rem;">
                <i class="fa fa-check-circle"></i> ✅ Cambios aplicados y guardados automáticamente en servidor y GitHub
              </div>
              <button v-if="msg.code && !msg.applied" class="btn-apply-code" @click="applyCodeToEditor(msg.code)">
                <i class="fa fa-code"></i> Insertar en Editor
              </button>
            </div>
            <div v-if="aiThinking" class="chat-bubble agent thinking">
              <div class="typing-indicator">
                <span></span><span></span><span></span>
              </div>
              <span class="thinking-text">Analizando código con Antigravity IA...</span>
            </div>
          </div>

          <!-- Prompt Box -->
          <div class="prompt-container">
            <div class="context-pills mb-2">
              <span class="pill"><i class="fa fa-file-code-o"></i> {{ activeFilePath ? activeFilePath.split('/').pop() : 'General' }}</span>
              <span class="pill purple"><i class="fa fa-shield"></i> AGENTS.md</span>
            </div>

            <div class="input-wrapper">
              <textarea v-model="userPrompt" @keydown.enter.prevent="sendPrompt" placeholder="Instruye a Antigravity IA (ej: 'Corrige este método', 'Optimiza esta consulta SQL')..."></textarea>
              <button class="btn-send" :disabled="aiThinking || !userPrompt.trim()" @click="sendPrompt">
                <i class="fa" :class="aiThinking ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
              </button>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue';

// Recursive Tree Component
Vue.component('tree-item', {
  name: 'tree-item',
  props: ['item', 'activePath'],
  data() {
    return {
      isOpen: false
    };
  },
  template: `
    <div class="tree-node">
      <div class="node-row" :class="{'active': activePath === item.path}" @click="toggle" style="display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 0.84rem; color: #f8fafc !important;">
        <i v-if="item.is_dir" class="fa icon-folder" :class="isOpen ? 'fa-folder-open' : 'fa-folder'" style="font-size: 1rem; color: #f59e0b !important;"></i>
        <i v-else class="fa icon-file" :class="getFileIcon(item.name)" style="font-size: 0.9rem;"></i>
        <span class="node-name" style="color: #f8fafc !important; font-size: 0.84rem; font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif;">{{ item.name }}</span>
      </div>
      <div v-if="item.is_dir && isOpen" class="node-children" style="padding-left: 14px;">
        <tree-item v-for="child in item.children" :key="child.path" :item="child" :active-path="activePath" @open-file="$emit('open-file', $event)"></tree-item>
      </div>
    </div>
  `,
  methods: {
    toggle() {
      if (this.item.is_dir) {
        this.isOpen = !this.isOpen;
      } else {
        this.$emit('open-file', this.item.path);
      }
    },
    getFileIcon(filename) {
      if (filename.endsWith('.php')) return 'fa-file-code-o text-info';
      if (filename.endsWith('.vue')) return 'fa-file-code-o text-emerald';
      if (filename.endsWith('.js')) return 'fa-file-code-o text-amber';
      if (filename.endsWith('.json')) return 'fa-file-text-o text-amber';
      if (filename.endsWith('.css') || filename.endsWith('.scss')) return 'fa-css3 text-cyan';
      if (filename.endsWith('.md')) return 'fa-book text-slate';
      return 'fa-file-o text-slate';
    }
  }
});

export default {
  name: 'WebIde',
  props: ['user'],
  data() {
    return {
      treeData: [],
      filteredTree: [],
      fileSearch: '',
      loadingTree: false,
      activeFilePath: '',
      fileContent: '',
      editor: null,
      loadingFile: false,
      saving: false,
      deploying: false,
      isModified: false,
      syntaxStatus: null,
      chatMessages: [
        {
          role: 'agent',
          text: '¡Hola Superadministrador! Soy Antigravity IA Agent. Estoy listo para ayudarte a auditar, editar y optimizar el sistema en tiempo real.',
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        }
      ],
      userPrompt: '',
      aiThinking: false
    };
  },
  watch: {
    fileSearch(val) {
      this.filterTreeData(val);
    }
  },
  mounted() {
    this.fetchTree();
    this.loadMonacoScript();
    window.addEventListener('keydown', this.handleGlobalKeydown);
  },
  beforeDestroy() {
    window.removeEventListener('keydown', this.handleGlobalKeydown);
    if (this.editor) {
      this.editor.dispose();
    }
  },
  methods: {
    fetchTree() {
      this.loadingTree = true;
      axios.get('/superadmin/ide/tree')
        .then(res => {
          if (res.data.status === 'success') {
            this.treeData = res.data.tree;
            this.filteredTree = res.data.tree;
          }
        })
        .catch(err => {
          let msg = (err.response && err.response.data && err.response.data.message) ? err.response.data.message : (err.message || 'No se pudo cargar el árbol de archivos.');
          swal('Error al cargar archivos', msg, 'error');
        })
        .finally(() => {
          this.loadingTree = false;
        });
    },
    reloadTree() {
      this.fetchTree();
    },
    filterTreeData(search) {
      if (!search.trim()) {
        this.filteredTree = this.treeData;
        return;
      }
      const q = search.toLowerCase();
      const filterNodes = (nodes) => {
        let res = [];
        for (let n of nodes) {
          if (n.name.toLowerCase().includes(q)) {
            res.push(n);
          } else if (n.is_dir && n.children) {
            let matchedChildren = filterNodes(n.children);
            if (matchedChildren.length > 0) {
              res.push({ ...n, children: matchedChildren });
            }
          }
        }
        return res;
      };
      this.filteredTree = filterNodes(this.treeData);
    },
    loadMonacoScript() {
      if (window.monaco) {
        this.initMonaco();
        return;
      }
      const script = document.createElement('script');
      script.src = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.38.0/min/vs/loader.min.js';
      script.onload = () => {
        window.require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.38.0/min/vs' } });
        window.require(['vs/editor/editor.main'], () => {
          this.initMonaco();
        });
      };
      document.body.appendChild(script);
    },
    initMonaco() {
      const container = document.getElementById('monaco-editor-canvas');
      if (!container) return;

      this.editor = window.monaco.editor.create(container, {
        value: '// Selecciona un archivo en el explorador izquierdo para comenzar a editar.\n',
        language: 'php',
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: true },
        scrollBeyondLastLine: false,
        tabSize: 4
      });

      this.editor.onDidChangeModelContent(() => {
        this.isModified = true;
      });
    },
    openFile(path) {
      this.loadingFile = true;
      this.syntaxStatus = null;
      axios.get('/superadmin/ide/file', { params: { path } })
        .then(res => {
          if (res.data.status === 'success') {
            this.activeFilePath = res.data.path;
            this.fileContent = res.data.content;
            this.isModified = false;

            if (this.editor) {
              const lang = this.getLanguageFromExtension(res.data.extension);
              const model = window.monaco.editor.createModel(res.data.content, lang);
              this.editor.setModel(model);
            }
          }
        })
        .catch(err => {
          const msg = err.response && err.response.data && err.response.data.message ? err.response.data.message : 'Error abriendo archivo.';
          swal('Error', msg, 'error');
        })
        .finally(() => {
          this.loadingFile = false;
        });
    },
    getLanguageFromExtension(ext) {
      switch (ext) {
        case 'php': return 'php';
        case 'js': return 'javascript';
        case 'vue': return 'html';
        case 'json': return 'json';
        case 'html': return 'html';
        case 'css': return 'css';
        case 'sql': return 'sql';
        case 'md': return 'markdown';
        default: return 'plaintext';
      }
    },
    saveActiveFile() {
      if (!this.activeFilePath || !this.editor) return;

      this.saving = true;
      const content = this.editor.getValue();

      axios.post('/superadmin/ide/save', { path: this.activeFilePath, content })
        .then(res => {
          if (res.data.status === 'success') {
            this.isModified = false;
            this.syntaxStatus = { type: 'success', message: 'Sintaxis OK & Guardado' };
            toast && toast.fire ? toast.fire({ type: 'success', title: 'Archivo guardado correctamente' }) : swal('Éxito', 'Archivo guardado', 'success');
          }
        })
        .catch(err => {
          const resData = err.response ? err.response.data : null;
          if (resData && resData.status === 'syntax_error') {
            this.syntaxStatus = { type: 'error', message: 'Error Sintaxis' };
            swal('Error de Sintaxis PHP', resData.details || resData.message, 'error');
          } else {
            swal('Error', (resData && resData.message) || 'Error al guardar.', 'error');
          }
        })
        .finally(() => {
          this.saving = false;
        });
    },
    deployFtp() {
      swal({
        title: '¿Desplegar a Producción?',
        text: 'Se subirán todos los archivos modificados a sistema.empaqueslupa.com mediante FTP.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Sí, Desplegar Ahora',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.value) {
          this.deploying = true;
          axios.post('/superadmin/ide/deploy-ftp')
            .then(res => {
              swal('Despliegue Exitoso', res.data.message || 'Archivos subidos al FTP', 'success');
            })
            .catch(err => {
              const log = err.response && err.response.data && err.response.data.log ? err.response.data.log : 'Error en FTP.';
              swal('Error en Despliegue', log, 'error');
            })
            .finally(() => {
              this.deploying = false;
            });
        }
      });
    },
    handleGlobalKeydown(e) {
      if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        this.saveActiveFile();
      }
    },
    sendPrompt() {
      const prompt = this.userPrompt.trim();
      if (!prompt || this.aiThinking) return;

      const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      this.chatMessages.push({ role: 'user', text: prompt, time });
      this.userPrompt = '';
      this.aiThinking = true;

      let selectedCode = '';
      if (this.editor) {
        const selection = this.editor.getSelection();
        if (selection && !selection.isEmpty()) {
          selectedCode = this.editor.getModel().getValueInRange(selection);
        }
      }

      axios.post('/superadmin/ide/ai-prompt', {
        prompt,
        active_file: this.activeFilePath,
        file_content: this.editor ? this.editor.getValue() : '',
        selected_code: selectedCode
      })
      .then(res => {
        if (res.data.status === 'success') {
          if (res.data.target_file && res.data.target_file !== this.activeFilePath) {
            this.openFile(res.data.target_file);
          } else if (res.data.applied && res.data.modified_code && this.editor) {
            this.editor.setValue(res.data.modified_code);
            this.isModified = false;
            this.syntaxStatus = { type: 'success', message: 'IA: Auto-aplicado & Guardado' };
          }

          if (res.data.applied && typeof toast !== 'undefined' && toast.fire) {
            toast.fire({ type: 'success', title: 'Antigravity IA localizó, modificó y guardó los cambios automáticamente' });
          }

          this.chatMessages.push({
            role: 'agent',
            text: res.data.response,
            code: res.data.applied ? null : res.data.modified_code,
            applied: res.data.applied,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
          });
        }
      })
      .catch(err => {
        this.chatMessages.push({
          role: 'agent',
          text: 'Disculpa, ocurrió un error procesando tu consulta con Antigravity IA.',
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
      })
      .finally(() => {
        this.aiThinking = false;
        this.$nextTick(() => {
          if (this.$refs.chatHistoryRef) {
            this.$refs.chatHistoryRef.scrollTop = this.$refs.chatHistoryRef.scrollHeight;
          }
        });
      });
    },
    applyCodeToEditor(code) {
      if (!this.editor || !code) return;
      this.editor.setValue(code);
      this.isModified = true;
      swal('Código Aplicado', 'El código sugerido por Antigravity IA ha sido insertado en el editor.', 'info');
    },
    clearChat() {
      this.chatMessages = [
        {
          role: 'agent',
          text: 'Conversación reiniciada. ¿En qué te ayudo ahora?',
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        }
      ];
    },
    formatMessageText(text) {
      if (!text) return '';
      return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br/>')
        .replace(/`([^`]+)`/g, '<code class="inline-code">$1</code>');
    }
  }
};
</script>

<style>
/* Custom Dark Scrollbars */
.tree-viewport::-webkit-scrollbar,
.chat-viewport::-webkit-scrollbar {
  width: 8px;
}
.tree-viewport::-webkit-scrollbar-track,
.chat-viewport::-webkit-scrollbar-track {
  background: #0f172a;
}
.tree-viewport::-webkit-scrollbar-thumb,
.chat-viewport::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 4px;
}
.tree-viewport::-webkit-scrollbar-thumb:hover,
.chat-viewport::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

.node-row:hover {
  background: #1e293b !important;
  color: #ffffff !important;
}
.node-row.active {
  background: rgba(99, 102, 241, 0.3) !important;
  color: #818cf8 !important;
}
.node-row.active .node-name {
  color: #818cf8 !important;
}

.web-ide-wrapper {
  width: 100%;
  height: calc(100vh - 65px);
  background-color: #0b0f19;
  padding: 8px;
  box-sizing: border-box;
}

.web-ide-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  width: 100%;
  background: #0f172a;
  border-radius: 12px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
  border: 1px solid #1e293b;
  overflow: hidden;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Header Bar */
.ide-header {
  height: 52px;
  background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  border-bottom: 1px solid #334155;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(99, 102, 241, 0.15);
  border: 1px solid rgba(99, 102, 241, 0.4);
  padding: 4px 10px;
  border-radius: 20px;
}

.brand-icon {
  font-size: 14px;
}

.brand-title {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  color: #818cf8;
}

.active-file-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #1e293b;
  padding: 4px 12px;
  border-radius: 6px;
  font-size: 0.82rem;
  color: #f1f5f9;
  border: 1px solid #334155;
}

.active-file-indicator.empty {
  color: #64748b;
}

.file-icon {
  color: #38bdf8;
}

.dot-modified {
  width: 8px;
  height: 8px;
  background-color: #f59e0b;
  border-radius: 50%;
  box-shadow: 0 0 8px #f59e0b;
}

.status-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-pill.success {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-pill.error {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-ide {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.btn-save {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: #ffffff;
  box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
}

.btn-save:hover:not(:disabled) {
  background: linear-gradient(135deg, #047857 0%, #059669 100%);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-deploy {
  background: #1e293b;
  color: #38bdf8;
  border: 1px solid #0284c7;
}

.btn-deploy:hover:not(:disabled) {
  background: #0284c7;
  color: #ffffff;
}

.btn-icon {
  background: #1e293b;
  color: #94a3b8;
  border: 1px solid #334155;
  padding: 6px 10px;
}

.btn-icon:hover {
  color: #ffffff;
  background: #334155;
}

/* Main Workspace */
.ide-workspace {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* Sidebar Explorer */
.explorer-sidebar {
  width: 270px;
  min-width: 240px;
  background: #0f172a;
  border-right: 1px solid #1e293b;
  display: flex;
  flex-direction: column;
}

.explorer-title {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  border-bottom: 1px solid #1e293b;
}

.file-count {
  background: #1e293b;
  color: #94a3b8;
  padding: 2px 6px;
  border-radius: 10px;
}

.search-box {
  position: relative;
  padding: 8px;
}

.search-box input {
  width: 100%;
  background: #1e293b;
  border: 1px solid #334155;
  color: #f8fafc;
  padding: 6px 28px 6px 28px;
  border-radius: 6px;
  font-size: 0.8rem;
  outline: none;
}

.search-box input:focus {
  border-color: #6366f1;
}

.search-icon {
  position: absolute;
  left: 16px;
  top: 16px;
  font-size: 0.8rem;
  color: #64748b;
}

.clear-search {
  position: absolute;
  right: 16px;
  top: 16px;
  font-size: 0.8rem;
  color: #64748b;
  cursor: pointer;
}

.tree-viewport {
  flex: 1;
  overflow-y: auto;
  padding: 4px;
}

/* Tree Nodes */
.tree-node {
  user-select: none;
}

.node-row {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 8px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.82rem;
  color: #cbd5e1;
  transition: background 0.15s ease;
}

.node-row:hover {
  background: #1e293b;
  color: #ffffff;
}

.node-row.active {
  background: rgba(99, 102, 241, 0.25);
  color: #818cf8;
  font-weight: 600;
}

.node-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.node-children {
  padding-left: 14px;
}

.text-emerald { color: #10b981; }
.text-amber { color: #f59e0b; }
.text-cyan { color: #06b6d4; }
.text-slate { color: #64748b; }

/* Editor Viewport */
.editor-viewport {
  flex: 1;
  background: #1e1e1e;
  position: relative;
  display: flex;
  flex-direction: column;
}

.monaco-canvas {
  width: 100%;
  height: 100%;
}

.editor-loading-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(4px);
  z-index: 20;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #38bdf8;
  font-weight: 600;
}

.welcome-screen {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at center, #1e1b4b 0%, #0f172a 70%);
  color: #f8fafc;
  padding: 20px;
}

.welcome-card {
  text-align: center;
  max-width: 420px;
}

.welcome-logo {
  font-size: 3rem;
  margin-bottom: 12px;
}

.welcome-card h2 {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 8px;
  color: #818cf8;
}

.welcome-card p {
  color: #94a3b8;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 20px;
}

.shortcuts {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.shortcut {
  background: rgba(255, 255, 255, 0.05);
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 0.8rem;
  color: #cbd5e1;
}

.shortcut code {
  background: #6366f1;
  color: #ffffff;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.75rem;
  margin-right: 6px;
}

/* AI Sidebar */
.ai-sidebar {
  width: 360px;
  min-width: 310px;
  background: #0f172a;
  border-left: 1px solid #1e293b;
  display: flex;
  flex-direction: column;
}

.ai-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 14px;
  background: #1e293b;
  border-bottom: 1px solid #334155;
}

.agent-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.agent-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  box-shadow: 0 0 12px rgba(168, 85, 247, 0.4);
}

.agent-name {
  font-size: 0.85rem;
  font-weight: 700;
  color: #f8fafc;
}

.agent-status {
  font-size: 0.7rem;
  color: #34d399;
  display: flex;
  align-items: center;
  gap: 6px;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  background-color: #34d399;
  border-radius: 50%;
  box-shadow: 0 0 8px #34d399;
}

.btn-icon-subtle {
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
}

.btn-icon-subtle:hover {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.chat-viewport {
  flex: 1;
  overflow-y: auto;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #0b0f19;
}

.chat-bubble {
  max-width: 90%;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 0.82rem;
  line-height: 1.45;
}

.chat-bubble.user {
  align-self: flex-end;
  background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
  color: #ffffff;
  border-bottom-right-radius: 2px;
}

.chat-bubble.agent {
  align-self: flex-start;
  background: #1e293b;
  color: #e2e8f0;
  border: 1px solid #334155;
  border-bottom-left-radius: 2px;
}

.bubble-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
  font-size: 0.7rem;
  opacity: 0.75;
}

.bubble-content {
  word-break: break-word;
}

.btn-apply-code {
  margin-top: 8px;
  width: 100%;
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.4);
  padding: 6px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-apply-code:hover {
  background: #10b981;
  color: #ffffff;
}

/* Prompt Box */
.prompt-container {
  padding: 12px;
  background: #0f172a;
  border-top: 1px solid #1e293b;
}

.context-pills {
  display: flex;
  gap: 6px;
}

.pill {
  background: #1e293b;
  color: #94a3b8;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.7rem;
  border: 1px solid #334155;
}

.pill.purple {
  color: #c084fc;
  border-color: rgba(192, 132, 252, 0.3);
}

.input-wrapper {
  position: relative;
  display: flex;
  gap: 8px;
}

.input-wrapper textarea {
  flex: 1;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 8px;
  color: #f8fafc;
  padding: 8px 10px;
  font-size: 0.82rem;
  resize: none;
  height: 60px;
  outline: none;
}

.input-wrapper textarea:focus {
  border-color: #6366f1;
}

.btn-send {
  width: 44px;
  height: 60px;
  background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1rem;
  transition: opacity 0.2s;
}

.btn-send:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner-neon {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(56, 189, 248, 0.2);
  border-top-color: #38bdf8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
