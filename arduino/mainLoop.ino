#include <WiFiNINA.h>            
#include <DHT.h>                 
#include <NTPClient.h>            
#include <WiFiUdp.h>              
#include <TimeLib.h>              

// Definição constante das portas (pins) físicas do Arduino
#define DHTPIN 0                  // Pin digital onde o pino de dados do sensor DHT está ligado
#define DHTTYPE DHT11             // Define o modelo exato do sensor DHT utilizado (DHT11)
#define BUZZER_PIN 1              // Pin digital onde o Buzzer (campainha/alarme) está ligado
#define LED_PIN 2                 // Pin digital onde o LED (luz) está ligado
#define CHAMA_PIN 3               // Pin digital onde o sensor de chama (fogo) está ligado

WiFiUDP clienteUDP;
char NTP_SERVER[] = "ntp.ipleiria.pt";                     // Servidor NTP do Politécnico de Leiria
NTPClient clienteNTP(clienteUDP, NTP_SERVER, 3600);        // Inicializa o cliente NTP com fuso horário ajustado (+3600 segundos = +1 hora)

// Credenciais da rede Wi-Fi e configurações do servidor de destino
char SSID[] = "labs";                                      // Nome da rede Wi-Fi à qual o Arduino se vai ligar
char PASS_WIFI[] = "1nv3nt@r2023_IPLEIRIA";                // Palavra-passe da rede Wi-Fi
char HOST[] = "iot.dei.estg.ipleiria.pt";                  // Endereço (domínio) do servidor onde está alojada a API
int PORTO = 80;                                            // Porta de comunicação web padrão para HTTP (não seguro)

// Inicialização dos objetos globais
DHT dht(DHTPIN, DHTTYPE);                                  // Cria o objeto do sensor DHT com os pinos e tipo definidos acima
WiFiClient clienteWifi;                                    // Cria o cliente de rede base
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO); // Cria o cliente HTTP preparado para enviar os dados para o HOST

// Função responsável por enviar dados dos sensores para a API (via método POST)
void post2api(String enviaNome, String enviaValor, String enviaHora){
  // Caminho exato do ficheiro PHP no servidor que recebe os dados
  String URLPath = "ti/ti016/ti/api/api.php";
  // Define o tipo de conteúdo que o servidor deve esperar (formato de formulário padrão)
  String contentType = "application/x-www-form-urlencoded";
  // Constrói o corpo da mensagem juntando as variáveis de nome, valor e hora
  String body = "nome="+enviaNome+"&valor="+enviaValor+"&hora="+enviaHora;
  
  // Executa efetivamente o pedido POST com os dados construídos
  clienteHTTP.post(URLPath, contentType, body);
  
  // Aguarda enquanto a ligação com o servidor estiver ativa para ler a resposta
  while(clienteHTTP.connected()){
    // Verifica se existem dados de resposta disponíveis para serem lidos
    if (clienteHTTP.available()){
      // Lê o código de estado HTTP (ex: 200 significa OK)
      int responseStatusCode = clienteHTTP.responseStatusCode();
      // Lê o texto que o servidor respondeu
      String responseBody = clienteHTTP.responseBody();
      
      // Imprime o resultado na consola (Monitor Série) para depuração de erros
      Serial.println("Status Code: "+String(responseStatusCode)+" Resposta: "+responseBody);
    }
  }
}

// Função para obter a hora exata atualizada da internet e formatá-la
void update_time(char *datahora){
  // Pede a hora atualizada ao servidor NTP
  clienteNTP.update();
  // Obtém o tempo em formato "Epoch" (segundos desde 1 de Janeiro de 1970)
  unsigned long epochTime = clienteNTP.getEpochTime();
  
  // Formata a string colocando os dados em blocos (Ano-Mês-Dia Hora:Minuto:Segundo) e injeta na variável "datahora"
  sprintf(datahora, "%02d-%02d-%02d %02d:%02d:%02d", year(epochTime), month(epochTime), day(epochTime), hour(epochTime), minute(epochTime), second(epochTime));
} 

// Função para ler no servidor se o Buzzer deve estar ligado ou desligado (via método GET)
void getBuzzerStatus() {
  // Caminho da API incluindo o parâmetro de pesquisa (nome=campainha)
  String URLPath = "/ti/ti016/ti/api/api.php?nome=campainha"; 

  // Executa o pedido GET ao servidor
  clienteHTTP.get(URLPath);

  // Lê a resposta enviada pelo servidor
  int responseStatusCode = clienteHTTP.responseStatusCode();
  String responseBody = clienteHTTP.responseBody();
  
  // Se a comunicação correu bem (código 200 OK)
  if (responseStatusCode == 200) {
    Serial.println("Buzzer status lido com sucesso!");
    Serial.println("Resposta GET: " + responseBody);
    
    // Remove espaços em branco ou quebras de linha invisíveis da resposta que possam interferir na leitura
    responseBody.trim();
    
    // Verifica se a API respondeu exatamente "1" ou se a resposta em formato JSON/Texto contém a diretiva "1"
    if (responseBody == "1" || responseBody.indexOf("\"valor\":\"1\"") != -1) {
      // Se a resposta for 1, liga o Buzzer emitindo corrente lógica ALTA para o pino
      digitalWrite(BUZZER_PIN, HIGH);
      Serial.println("Buzzer: LIGADO");
    } else {
      // Caso contrário (se for 0 ou outra coisa), desliga o Buzzer cortando a corrente lógica
      digitalWrite(BUZZER_PIN, LOW);
      Serial.println("Buzzer: DESLIGADO");
    }
  } else {
    // Bloco caso haja um erro de rede (ex: servidor em baixo, erro 404, etc)
    Serial.print("Erro ao ler Buzzer. Status: ");
    Serial.println(responseStatusCode);
    // Por segurança e por não saber o estado real atual, garante que o Buzzer fica desligado
    digitalWrite(BUZZER_PIN, LOW);
  }

  // Encerra a ligação HTTP deste pedido para libertar recursos de memória
  clienteHTTP.stop(); 
}

// Função para ler no servidor se o LED deve estar ligado ou desligado (via método GET)
void getLed() {
  // O processo e a lógica aqui são exatamente iguais aos do getBuzzerStatus(), mas virados para o sensor "led"
  String URLPath = "/ti/ti016/ti/api/api.php?nome=led"; 

  clienteHTTP.get(URLPath);
  int responseStatusCode = clienteHTTP.responseStatusCode();
  String responseBody = clienteHTTP.responseBody();

  if (responseStatusCode == 200) {
    Serial.println("led status lido com sucesso!");
    Serial.println("Resposta GET: " + responseBody);

    // Remove espaços em branco ou quebras de linha invisíveis da resposta
    responseBody.trim();
    
    // Se a API responder "1", ativa fisicamente o LED
    if (responseBody == "1" || responseBody.indexOf("\"valor\":\"1\"") != -1) {
      digitalWrite(LED_PIN, HIGH);
      Serial.println("LED: LIGADO");
    } else {
      // Caso responda "0", desativa fisicamente o LED
      digitalWrite(LED_PIN, LOW);
      Serial.println("LED: DESLIGADO");
    }
  } else {
    Serial.print("Erro ao ler LED. Status: ");
    Serial.println(responseStatusCode);
    // Por segurança, se a comunicação falhar, apaga-se a luz
    digitalWrite(LED_PIN, LOW);
  }

  // Encerra a ligação
  clienteHTTP.stop();
}

// Função para avaliar a situação do sensor de chama (fogo)
// Se detetar chama (LOW), retorna 1 (perigo). Se não detetar, retorna 0 (seguro).
int sensor_chama(){
  // Lê o sinal digital do pino. A maioria destes sensores retorna LOW (0) quando detetam fogo ativo.
  int estadoSensor = digitalRead(CHAMA_PIN);
  
  if (estadoSensor == LOW) {
    // Alerta disparado
    Serial.println("ALERTA: Fogo Detetado!");
    return 1;
  } else {
    // Situação normalizada
    Serial.println("Ambiente Seguro.");
    return 0;
  }
}

// O bloco setup() corre apenas uma única vez quando o Arduino é ligado ou reiniciado
void setup() {
  // Variável para guardar temporariamente o estado da ligação Wi-Fi
  int status = WL_IDLE_STATUS;

  // Inicia a comunicação série com o computador a 115200 de velocidade (Baud Rate)
  Serial.begin(115200);
  
  // Espera que a porta série ligue e estabilize (útil para placas nativas USB)
  while (!Serial);
  
  Serial.println("Connecting to:");
  Serial.println(SSID);
  
  // Tenta ligar à rede Wi-Fi. Fica preso neste ciclo enquanto o status for diferente de "LIGADO"
  while (status != WL_CONNECTED) {
    Serial.print(".");
    // Inicia a negociação com as credenciais da rede
    status = WiFi.begin(SSID, PASS_WIFI);
    
    // Espera 5 segundos para dar tempo à placa de estabelecer a ligação rádio antes de tentar novamente
    delay(5000);
    
    // NOTA TÉCNICA: O "break" aqui cancela o ciclo imediatamente após a primeira tentativa.
    // Se o Wi-Fi falhar à primeira, ele não tentará de novo e avançará no código mesmo sem net.
    break; 
  }

  // Imprime informações de rede após a tentativa de ligação
  Serial.println(); // Dá uma quebra de linha para a formatação ficar limpa na consola
  Serial.println("done");
  Serial.println("WI-FI Connected");
  
  // Imprime o endereço IP que o Arduino recebeu do router local
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());

  Serial.print("NETMASK: ");
  Serial.println(WiFi.subnetMask());

  Serial.print("GATEWAY: ");
  Serial.println(WiFi.gatewayIP());

  // Imprime a força do sinal Wi-Fi detetado
  Serial.print("RSSI: ");
  Serial.println(WiFi.RSSI());

  // Configurações dos pinos físicos do Arduino
  pinMode(LED_BUILTIN, OUTPUT);             // Define o pino do LED interno do Arduino como uma Saída de corrente
  pinMode(CHAMA_PIN, INPUT);                // Define o pino do Sensor de Chama como uma Entrada de leitura de sinais
  pinMode(LED_PIN, OUTPUT);                 // Define o pino do LED de iluminação como uma Saída de corrente

  // Inicializa o sensor físico DHT de temperatura e humidade
  dht.begin(); 
  // Inicializa a ponte de ligação com o servidor das horas na internet
  clienteNTP.begin();
}

// O bloco loop() executa repetidamente até ao infinito enquanto o Arduino tiver energia
void loop() {
  // Solicita ao sensor DHT a leitura térmica e guarda o resultado (em graus Celsius)
  float temperatura = dht.readTemperature();
  // Solicita ao sensor DHT a leitura da humidade e guarda o resultado (em percentagem)
  float humidade = dht.readHumidity();
  
  // Se o valor retornado não for um número válido (Not a Number - isnan), há problema no cabo ou sensor
  if (isnan(temperatura) || isnan(humidade)) {
    Serial.println("Falha na leitura do sensor DHT!");
    return; // Cancela este ciclo imediatamente e recomeça para evitar processar erros
  }

  // Prepara as variáveis de nomes (chaves) para enviar para o servidor
  String nomeTemperatura = "temperatura";
  // Converte o valor de número (float) que saiu do sensor para formato texto (String)
  String valorTemperatura = String(temperatura);

  String nomeHumidade = "humidade";
  String valorHumidade = String(humidade);

  // Cria um pequeno espaço na memória (array de 20 caracteres) para guardar o carimbo da hora
  char datahora[20];
  // Invoca a função criada mais acima para ir à internet buscar a hora atual e preencher a variável datahora
  update_time(datahora);
  
  // Imprime os valores do DHT na consola do computador
  Serial.print("Temperatura: ");
  Serial.println(valorTemperatura);
  
  Serial.print("Humidade: ");
  Serial.println(valorHumidade);

  // Lê o estado atual do sensor de fogo (0 ou 1)
  int chama = sensor_chama();
  
  // Invoca a função post2api para enviar as 3 variáveis capturadas para a base de dados via HTTP POST
  post2api(String("alarme"), String(chama), datahora);
  post2api(nomeTemperatura, valorTemperatura, datahora);
  post2api(nomeHumidade, valorHumidade, datahora);

  // Verifica na plataforma o estado atualizado dos atuadores via HTTP GET para ligar/desligar componentes físicos
  getBuzzerStatus();
  getLed();
  
  // Pausa completamente o processador durante 5000 milissegundos (5 segundos) antes de recomeçar do início do loop
  delay(5000);  
}