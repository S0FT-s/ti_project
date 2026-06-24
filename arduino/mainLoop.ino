#include <WiFiNINA.h>
#include <ArduinoHttpClient.h>
#include <DHT.h> 
#include <NTPClient.h>
#include <WiFiUdp.h> //Pré-instalada com o Arduino IDE
#include <TimeLib.h>


#define DHTPIN 0 // Pin Digital onde está ligado o sensor
#define DHTTYPE DHT11 // Tipo de sensor DHT
#define BUZZER_PIN 1 // Pin Digital onde está ligado o buzzer 
#define LED_PIN 2
#define CHAMA_PIN 3

WiFiUDP clienteUDP;
char NTP_SERVER[] = "ntp.ipleiria.pt";
NTPClient clienteNTP(clienteUDP, NTP_SERVER, 3600);
char SSID[] = "labs";
char PASS_WIFI[] = "1nv3nt@r2023_IPLEIRIA";
char HOST[] = "iot.dei.estg.ipleiria.pt";
int PORTO = 80;

DHT dht(DHTPIN, DHTTYPE); 
WiFiClient clienteWifi;
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

void post2api(String enviaNome, String enviaValor, String enviaHora){
  String URLPath = "ti/ti016/ti/api/api.php";
  String contentType = "application/x-www-form-urlencoded";
  String body = "nome="+enviaNome+"&valor="+enviaValor+"&hora="+enviaHora;
  clienteHTTP.post(URLPath, contentType, body);
  while(clienteHTTP.connected()){
    if (clienteHTTP.available()){
      int responseStatusCode = clienteHTTP.responseStatusCode();
      String responseBody = clienteHTTP.responseBody();
      Serial.println("Status Code: "+String(responseStatusCode)+" Resposta: "+responseBody);
    }
  }
}

void update_time(char *datahora){
  clienteNTP.update();
  unsigned long epochTime = clienteNTP.getEpochTime();
  sprintf(datahora, "%02d-%02d-%02d %02d:%02d:%02d", year(epochTime), month(epochTime), day(epochTime), hour(epochTime), minute(epochTime), second(epochTime));
} 

void getBuzzerStatus() {
  String URLPath = "/ti/ti016/ti/api/api.php?nome=campainha"; 

  clienteHTTP.get(URLPath);

  int responseStatusCode = clienteHTTP.responseStatusCode();
  String responseBody = clienteHTTP.responseBody();

  if (responseStatusCode == 200) {
    Serial.println("Buzzer status lido com sucesso!");
    Serial.println("Resposta GET: " + responseBody);

    // Remove espaços em branco ou quebras de linha invisíveis da resposta
    responseBody.trim(); 

    // Se a API responder exatamente "1" (ou se a resposta contiver "1")
    if (responseBody == "1" || responseBody.indexOf("\"valor\":\"1\"") != -1) {
      digitalWrite(BUZZER_PIN, HIGH);
      Serial.println("Buzzer: LIGADO");
    } else {
      digitalWrite(BUZZER_PIN, LOW);
      Serial.println("Buzzer: DESLIGADO");
    }
  } else {
    Serial.print("Erro ao ler Buzzer. Status: ");
    Serial.println(responseStatusCode);
    digitalWrite(BUZZER_PIN, LOW); // Por segurança, desliga se falhar
  }

  clienteHTTP.stop(); 
}


void getLed() {
  String URLPath = "/ti/ti016/ti/api/api.php?nome=led"; 

  clienteHTTP.get(URLPath);

  int responseStatusCode = clienteHTTP.responseStatusCode();
  String responseBody = clienteHTTP.responseBody();

  if (responseStatusCode == 200) {
    Serial.println("led status lido com sucesso!");
    Serial.println("Resposta GET: " + responseBody);

    // Remove espaços em branco ou quebras de linha invisíveis da resposta
    responseBody.trim(); 

    // Se a API responder exatamente "1" (ou se a resposta contiver "1")
    if (responseBody == "1" || responseBody.indexOf("\"valor\":\"1\"") != -1) {
      digitalWrite(LED_PIN, HIGH);
      Serial.println("LED: LIGADO");
    } else {
      digitalWrite(LED_PIN, LOW);
      Serial.println("LED: DESLIGADO");
    }
  } else {
    Serial.print("Erro ao ler LED. Status: ");
    Serial.println(responseStatusCode);
    digitalWrite(LED_PIN, LOW); // Por segurança, desliga se falhar
  }

  clienteHTTP.stop(); 
}

//se retomar 0 tá arder se retonar 1 tá tudo bem
int sensor_chama(){
  int estadoSensor = digitalRead(CHAMA_PIN);

  if (estadoSensor == LOW) {
    Serial.println("ALERTA: Fogo Detetado!");
    return 1;
  } else {
    Serial.println("Ambiente Seguro.");
    return 0;
  }
}

void setup() {
  int status = WL_IDLE_STATUS;

  Serial.begin(115200);
  // Espera que a porta série ligue (útil para placas nativas USB)
  while (!Serial);
  
  Serial.println("Connecting to:");
  Serial.println(SSID);
  
  // Tenta ligar à rede WiFi enquanto o status for diferente de "LIGADO"
  while (status != WL_CONNECTED) {
    Serial.print(".");
    status = WiFi.begin(SSID, PASS_WIFI);
    
    // Espera 5 segundos para a ligação ter tempo de se estabelecer
    delay(5000);
    break; 
  }

  Serial.println(); // Dá uma quebra de linha para a formatação ficar limpa
  Serial.println("done");
  Serial.println("WI-FI Connected");

  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());

  Serial.print("NETMASK: ");
  Serial.println(WiFi.subnetMask());

  Serial.print("GATEWAY: ");
  Serial.println(WiFi.gatewayIP());

  Serial.print("RSSI: ");
  Serial.println(WiFi.RSSI());

  pinMode(LED_BUILTIN, OUTPUT);
  
  // Inicialização do sensor de temperatura/humidade e do relógio NTP
  dht.begin(); 
  clienteNTP.begin();

  pinMode(CHAMA_PIN, INPUT);
  pinMode(LED_PIN, OUTPUT);
}

void loop() {
  float temperatura = dht.readTemperature();
  float humidade = dht.readHumidity();

  if (isnan(temperatura) || isnan(humidade)) {
    Serial.println("Falha na leitura do sensor DHT!");
    return;
  }

  String nomeTemperatura = "temperatura";
  String valorTemperatura = String(temperatura);

  String nomeHumidade = "humidade";
  String valorHumidade = String(humidade);

  char datahora[20];
  update_time(datahora);
  
  Serial.print("Temperatura: ");
  Serial.println(valorTemperatura);
  
  Serial.print("Humidade: ");
  Serial.println(valorHumidade);

  // Envia os dados dos sensores (POST)
  int chama = sensor_chama();
  post2api(String("alarme"),String(chama),datahora);
  post2api(nomeTemperatura, valorTemperatura, datahora);
  post2api(nomeHumidade, valorHumidade, datahora);

  // Verifica o estado do buzzer na API (GET)
  getBuzzerStatus();
  getLed();
  delay(5000);  
}
