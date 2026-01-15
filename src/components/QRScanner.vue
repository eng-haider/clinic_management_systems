<template>
    <v-dialog v-model="dialog" max-width="600px" @click:outside="closeDialog">
        <v-card>
            <v-toolbar color="primary" dark>
                <v-icon left>mdi-qrcode-scan</v-icon>
                <v-toolbar-title>مسح رمز الاستجابة السريع (QR)</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click="closeDialog">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-toolbar>

            <v-card-text class="pa-4">
                <div v-if="!cameraReady && !error" class="text-center pa-8">
                    <v-progress-circular
                        indeterminate
                        color="primary"
                        size="64"
                    ></v-progress-circular>
                    <p class="mt-4">جاري تحضير الكاميرا...</p>
                </div>

                <div v-show="cameraReady && hasCamera && !error">
                    <qrcode-stream
                        @decode="onDecode"
                        @init="onInit"
                        :camera="camera"
                        class="qr-scanner-video"
                    >
                        <div v-if="scanning" class="scanning-overlay">
                            <div class="scanning-frame"></div>
                            <p class="scanning-text">وجه الكاميرا نحو رمز QR</p>
                        </div>
                    </qrcode-stream>
                </div>

                <v-alert
                    v-if="error"
                    type="error"
                    dismissible
                    @input="error = null"
                    class="mt-3"
                >
                    {{ error }}
                </v-alert>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="grey darken-1" text @click="closeDialog">
                    إلغاء
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { QrcodeStream } from 'vue-qrcode-reader'

export default {
    name: 'QRScanner',
    components: {
        QrcodeStream
    },
    props: {
        value: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            camera: 'auto',
            scanning: true,
            cameraReady: false,
            hasCamera: true,
            error: null
        }
    },
    computed: {
        dialog: {
            get() {
                return this.value
            },
            set(val) {
                this.$emit('input', val)
            }
        }
    },
    methods: {
        async onInit(promise) {
            try {
                this.error = null
                await promise
                this.cameraReady = true
                this.hasCamera = true
            } catch (error) {
                this.cameraReady = true
                this.hasCamera = false
                
                if (error.name === 'NotAllowedError') {
                    this.error = 'لم يتم منح إذن الوصول إلى الكاميرا'
                } else if (error.name === 'NotFoundError') {
                    this.error = 'لم يتم العثور على كاميرا'
                } else if (error.name === 'NotSupportedError') {
                    this.error = 'يجب استخدام اتصال آمن (HTTPS)'
                } else if (error.name === 'NotReadableError') {
                    this.error = 'الكاميرا مستخدمة بالفعل'
                } else if (error.name === 'OverconstrainedError') {
                    this.error = 'الكاميرا غير متوافقة'
                } else {
                    this.error = 'خطأ في تشغيل الكاميرا: ' + error.message
                }
                console.error('Camera error:', error)
            }
        },
        onDecode(result) {
            if (result) {
                this.scanning = false
                this.camera = 'off'
                
                // Emit the scanned result
                this.$emit('scanned', result)
                
                // Close the dialog
                this.closeDialog()
            }
        },
        closeDialog() {
            this.camera = 'off'
            this.scanning = false
            this.cameraReady = false
            this.hasCamera = true
            this.error = null
            this.$emit('input', false)
        },
        resetScanner() {
            this.camera = 'auto'
            this.scanning = true
            this.cameraReady = false
            this.hasCamera = true
            this.error = null
        }
    },
    watch: {
        value(newVal) {
            if (newVal) {
                this.resetScanner()
            } else {
                this.camera = 'off'
            }
        }
    }
}
</script>

<style scoped>
.qr-scanner-video {
    width: 100%;
    height: 400px;
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background: #000;
}

.scanning-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.scanning-frame {
    width: 250px;
    height: 250px;
    border: 3px solid #fff;
    border-radius: 12px;
    box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5);
    animation: pulse 2s ease-in-out infinite;
}

.scanning-text {
    color: #fff;
    font-size: 18px;
    font-weight: bold;
    margin-top: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    font-family: 'Cairo', sans-serif;
}

@keyframes pulse {
    0%, 100% {
        border-color: #fff;
        box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.5);
    }
    50% {
        border-color: #4CAF50;
        box-shadow: 0 0 0 2000px rgba(0, 0, 0, 0.3), 0 0 20px #4CAF50;
    }
}

/* Mobile responsive */
@media (max-width: 600px) {
    .qr-scanner-video {
        height: 300px;
    }
    
    .scanning-frame {
        width: 200px;
        height: 200px;
    }
    
    .scanning-text {
        font-size: 16px;
    }
}
</style>
