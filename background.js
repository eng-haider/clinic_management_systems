'use strict'

const { app, protocol, BrowserWindow } = require('electron')
const path = require('path')
const fs = require('fs')

// Keep a global reference of the window object
let win

// Scheme must be registered before the app is ready
protocol.registerSchemesAsPrivileged([
  { scheme: 'app', privileges: { secure: true, standard: true } }
])

function createWindow() {
  // Create the browser window
  win = new BrowserWindow({
    width: 1200,
    height: 800,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
      enableRemoteModule: true,
      webSecurity: false
    }
  })

  // Load the built dist folder
  const indexPath = path.join(__dirname, 'dist', 'index.html')
  console.log('Loading from:', indexPath)
  console.log('File exists:', fs.existsSync(indexPath))
  
  if (fs.existsSync(indexPath)) {
    win.loadFile(indexPath)
    
    // Log console messages from renderer to terminal
    win.webContents.on('console-message', (event, level, message, line, sourceId) => {
      console.log(`[Renderer Console] ${message}`)
      if (level === 3) { // Error level
        console.error(`[Renderer Error] ${message} (${sourceId}:${line})`)
      }
    })
    
    // Log any page load errors
    win.webContents.on('did-fail-load', (event, errorCode, errorDescription) => {
      console.error('[Page Load Error]', errorCode, errorDescription)
    })
  } else {
    console.error('index.html not found at:', indexPath)
  }
  
  // Open DevTools for debugging
  win.webContents.openDevTools()

  win.on('closed', () => {
    win = null
  })
}

// Quit when all windows are closed
app.on('window-all-closed', () => {
  // On macOS it is common for applications to stay open
  // until the user explicitly quits
  if (process.platform !== 'darwin') {
    app.quit()
  }
})

app.on('activate', () => {
  // On macOS it's common to re-create a window when the
  // dock icon is clicked
  if (win === null) {
    createWindow()
  }
})

// This method will be called when Electron has finished
// initialization and is ready to create browser windows
app.on('ready', async () => {
  createWindow()
})

// Exit cleanly on request from parent process in development mode
if (process.env.NODE_ENV !== 'production') {
  if (process.platform === 'win32') {
    process.on('message', (data) => {
      if (data === 'graceful-exit') {
        app.quit()
      }
    })
  } else {
    process.on('SIGTERM', () => {
      app.quit()
    })
  }
}
