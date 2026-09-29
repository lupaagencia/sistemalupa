<template>
  <div class="web-ide-container d-flex flex-column h-100">
    <!-- Header Bar -->
    <div class="ide-header bg-dark text-white px-3 py-2 d-flex align-items-center justify-content-between border-bottom border-secondary">
      <div class="d-flex align-items-center">
        <span class="badge badge-info mr-2 px-2 py-1 font-weight-bold" style="font-size: 0.85rem;">
          <i class="fa fa-code"></i> WEB IDE & AI AGENT
        </span>
        <span class="text-light font-weight-bold mr-3" style="font-size: 0.9rem;">
          <i class="fa fa-folder-open text-warning mr-1"></i> {{ activeFilePath || 'Selecciona un archivo del proyecto' }}
        </span>
        <span v-if="isModified" class="badge badge-warning">Modificado</span>
        <span v-if="syntaxStatus" :class="['badge mr-2', syntaxStatus.type === 'error' ? 'badge-danger' : 'badge-success']">
          {{ syntaxStatus.message }}
        </span>
      </div>

      <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-success mr-2 font-weight-bold" :disabled="saving || !activeFilePath" @click="saveActiveFile">
          <i class="fa" :class="saving ? 'fa-spinner fa-spin' : 'fa-save'"></i> {{ saving ? 'Guardando...' : 'Guardar (Ctrl+S)' }}
        </button>
        <button class="btn btn-sm btn-outline-info mr-2 font-weight-bold" :disabled="deploying" @click="deployFtp">
          <i class="fa" :class="deploying ? 'fa-spinner fa-spin' : 'fa-cloud-upload'"></i> {{ deploying ? 'Desplegando...' : 'Desplegar a FTP' }}
        </button>
        <button class="btn btn-sm btn-outline-light" @click="reloadTree" title="Recargar Árbol">
          <i class="fa fa-refresh"></i>
        </button>
      </div>
    </div>

    <!-- Main Workspace Layout -->
    <div class="ide-body d-flex flex-row flex-grow-1" style="height: calc(100vh - 120px); overflow: hidden;">
      
      <!-- Left Panel: File Explorer -->
      <div class="file-explorer bg-dark text-light border-right border-secondary p-2 d-flex flex-column" style="width: 280px; min-width: 250px;">
        <div class="explorer-header mb-2">
          <div class="input-group input-group-sm">
            <input type="text" v-model="fileSearch" class="form-control bg-secondary text-white border-0" placeholder="Buscar archivo..." />
            <div class="input-group-append">
              <span class="input-group-text bg-secondary text-light border-0"><i class="fa fa-search"></i></span>
            </div>
          </div>
        </div>

        <div class="tree-container flex-grow-1 overflow-auto small">
          <div v-if="loadingTree" class="text-center py-4 text-muted">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <div class="mt-2">Cargando proyecto...</div>
          </div>
          <div v-else>
            <tree-item v-for="node in filteredTree" :key="node.path" :item="node" :active-path="activeFilePath" @open-file="openFile"></tree-item>
          </div>
        </div>
      </div>

      <!-- Center Panel: Monaco Editor -->
      <div class="editor-container flex-grow-1 d-flex flex-column bg-dark" style="position: relative;">
        <div v-show="loadingFile" class="editor-loader position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark text-light" style="z-index: 10; opacity: 0.9;">
          <i class="fa fa-spinner fa-spin fa-3x text-info mb-2"></i>
          <div>Abriendo {{ activeFilePath }}...</div>
        </div>
        <div id="monaco-editor-canvas" class="w-100 h-100"></div>
      </div>

      <!-- Right Panel: Antigravity AI Agent Chat -->
      <div class="ai-agent-panel bg-dark text-light border-left border-secondary d-flex flex-column p-2" style="width: 380px; min-width: 320px;">
        <div class="agent-header d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-secondary">
          <div class="d-flex align-items-center">
            <div class="agent-avatar mr-2 rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 14px;">
              ⚡
            </div>
            <div>
              <div class="font-weight-bold" style="font-size: 0.9rem;">Antigravity IA Agent</div>
              <div class="text-success small" style="font-size: 0.75rem;"><i class="fa fa-circle"></i> Pair Programmer Activo</div>
            </div>
          </div>
          <button class="btn btn-sm btn-link text-light p-0" @click="clearChat" title="Limpiar conversación">
            <i class="fa fa-trash"></i>
          </button>
        </div>

        <!-- Chat History -->
        <div class="chat-history flex-grow-1 overflow-auto mb-2 p-2 rounded bg-dark border border-secondary" ref="chatHistoryRef">
          <div v-for="(msg, idx) in chatMessages" :key="idx" :class="['chat-bubble mb-3 p-2 rounded small', msg.role === 'user' ? 'bg-primary text-white ml-4' : 'bg-secondary text-light mr-4']">
            <div class="d-flex align-items-center justify-content-between font-weight-bold mb-1" style="font-size: 0.75rem; opacity: 0.8;">
              <span>{{ msg.role === 'user' ? 'Superadministrador' : '⚡ Antigravity Agent' }}</span>
              <span>{{ msg.time }}</span>
            </div>
            <div class="chat-text" style="white-space: pre-wrap; word-break: break-word;">{{ msg.text }}</div>
            <button v-if="msg.code" class="btn btn-xs btn-success mt-2 font-weight-bold w-100" @click="applyCodeToEditor(msg.code)">
              <i class="fa fa-paste"></i> Aplicar Código en Editor
            </button>
          </div>
          <div v-if="aiThinking" class="text-info small p-2 text-center">
            <i class="fa fa-spinner fa-spin mr-1"></i> Antigravity IA está analizando tu código...
          </div>
        </div>

        <!-- Context Badges -->
        <div class="mb-2 d-flex flex-wrap align-items-center" style="gap: 4px;">
          <span class="badge badge-pill badge-secondary" style="font-size: 0.7rem;">
            <i class="fa fa-file-text-o"></i> {{ activeFilePath ? activeFilePath.split('/').pop() : 'Sin archivo' }}
          </span>
          <span class="badge badge-pill badge-info" style="font-size: 0.7rem;">
            <i class="fa fa-book"></i> AGENTS.md
          </span>
        </div>

        <!-- Prompt Input Area -->
        <div class="prompt-area">
          <textarea v-model="userPrompt" @keydown.enter.prevent="sendPrompt" class="form-control bg-secondary text-white border-0 mb-2 small" rows="3" placeholder="Pídele algo a Antigravity IA (ej: 'Agrega un método para exportar a PDF', 'Corrige errores en este archivo')..."></textarea>
          <button class="btn btn-info btn-block font-weight-bold text-white btn-sm" :disabled="aiThinking || !userPrompt.trim()" @click="sendPrompt">
            <i class="fa" :class="aiThinking ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i> {{ aiThinking ? 'Procesando...' : 'Enviar a Antigravity IA' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import Vue from 'vue';

// Recursive Tree Item Component
Vue.component('tree-item', {
  name: 'tree-item',
  props: ['item', 'activePath'],
  data() {
    return {
      isOpen: false
    };
  },
  template: `
    <div class="tree-item">
      <div class="d-flex align-items-center py-1 px-2 rounded hover-bg cursor-pointer"
           :class="{'bg-secondary text-white font-weight-bold': activePath === item.path}"
           @click="toggle">
        <i v-if="item.is_dir" class="fa mr-2 text-warning" :class="isOpen ? 'fa-folder-open' : 'fa-folder'"></i>
        <i v-else class="fa mr-2" :class="getFileIcon(item.name)"></i>
        <span class="text-truncate" style="max-width: 200px;">{{ item.name }}</span>
      </div>
      <div v-if="item.is_dir && isOpen" class="pl-3">
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
      if (filename.endsWith('.vue')) return 'fa-file-code-o text-success';
      if (filename.endsWith('.js')) return 'fa-file-code-o text-warning';
      if (filename.endsWith('.json')) return 'fa-file-text-o text-warning';
      if (filename.endsWith('.css') || filename.endsWith('.scss')) return 'fa-css3 text-primary';
      if (filename.endsWith('.md')) return 'fa-book text-light';
      return 'fa-file-o text-muted';
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
          text: '¡Hola Superadministrador! Soy Antigravity IA Agent. Estoy listo para asistirte y pair-programar contigo directamente en la web.',
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
            this.syntaxStatus = { type: 'success', message: 'Guardado & Sintaxis OK' };
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
        confirmButtonColor: '#28a745',
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
          this.chatMessages.push({
            role: 'agent',
            text: res.data.response,
            code: res.data.suggested_code,
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
    }
  }
};
</script>

<style scoped>
.web-ide-container {
  height: 100vh;
  background-color: #1e1e1e;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.hover-bg:hover {
  background-color: #2a2d2e;
}
.cursor-pointer {
  cursor: pointer;
}
.chat-history {
  background-color: #181818 !important;
}
.btn-xs {
  padding: 0.25rem 0.4rem;
  font-size: 0.75rem;
}
</style>
