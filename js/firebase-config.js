// 🔥 Configuração Firebase para o Restaurante 100 Sabores

// SUBSTITUIR PELOS TEUS DADOS REAIS DO FIREBASE
const firebaseConfig = {
  apiKey: "SUA_API_KEY_AQUI",
  authDomain: "seu-projeto.firebaseapp.com",
  projectId: "seu-projeto-id",
  storageBucket: "seu-projeto.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abcdef123456"
};

// Inicializar Firebase
firebase.initializeApp(firebaseConfig);

// Referência para a base de dados Firestore
const db = firebase.firestore();

// Função para salvar reserva na base de dados
async function salvarReserva(dadosReserva) {
    try {
        // Adicionar timestamp
        dadosReserva.timestamp = firebase.firestore.FieldValue.serverTimestamp();
        dadosReserva.status = 'pendente'; // pendente, confirmada, cancelada
        
        // Salvar na coleção 'reservas'
        const docRef = await db.collection('reservas').add(dadosReserva);
        
        console.log('Reserva salva com ID:', docRef.id);
        return { success: true, id: docRef.id };
        
    } catch (error) {
        console.error('Erro ao salvar reserva:', error);
        return { success: false, error: error.message };
    }
}

// Função para obter todas as reservas (para o admin)
async function obterReservas() {
    try {
        const snapshot = await db.collection('reservas')
            .orderBy('timestamp', 'desc')
            .get();
            
        const reservas = [];
        snapshot.forEach(doc => {
            reservas.push({
                id: doc.id,
                ...doc.data()
            });
        });
        
        return reservas;
    } catch (error) {
        console.error('Erro ao obter reservas:', error);
        return [];
    }
}

// Função para atualizar status de uma reserva
async function atualizarStatusReserva(idReserva, novoStatus) {
    try {
        await db.collection('reservas').doc(idReserva).update({
            status: novoStatus,
            atualizadoEm: firebase.firestore.FieldValue.serverTimestamp()
        });
        
        return { success: true };
    } catch (error) {
        console.error('Erro ao atualizar reserva:', error);
        return { success: false, error: error.message };
    }
}

// Função para enviar email de confirmação (via EmailJS ou outro serviço)
async function enviarEmailConfirmacao(dadosReserva) {
    // Implementar integração com EmailJS ou outro serviço
    console.log('Enviando email de confirmação para:', dadosReserva.email);
    
    // Por agora, vamos usar o sistema que já temos
    // Pode integrar com EmailJS, SendGrid, etc.
}

// Exportar funções para uso global
window.firebaseDB = {
    salvarReserva,
    obterReservas,
    atualizarStatusReserva,
    enviarEmailConfirmacao
};
