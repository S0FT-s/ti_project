#include <WiFi101.h>
#include <ArduinoHttpClient.h>
#include <DHT.h>
#include <NTPClient.h>
#include <WiFiUdp.h> 
#include <TimeLib.h>

#define DHTPIN 0 // Pin Digital onde está ligado o sensor
#define DHTTYPE DHT11 // Tipo de sensor DHT
#define BUZZER_PIN 1 // Pin Digital onde está ligado o buzzer (Mude se necessário)

WiFiUDP clienteUDP;
char NTP_SERVER[] = "ntp.ipleiria.pt";
NTPClient clienteNTP(clienteUDP, NTP_SERVER, 3600);
char SSID[] = "labs";
char PASS_WIFI[] = "1nv3nt@r2023_IPLEIRIA";

DHT dht(DHTPIN, DHTTYPE); 

char HOST[] = "iot.dei.estg.ipleiria.pt";
int PORTO = 80;
WiFiClient clienteWifi;

HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

void setup() {
  Serial.begin(115200);
  while (!Serial);  

  WiFi.begin(SSID, PASS_WIFI);
  pinMode(LED_BUILTIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT); // Configura o pino do buzzer como saída

  while (WiFi.status() != WL_CONNECTED) {
    Serial.println(".");
    delay(500);
  }

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
  
  dht.begin();
  clienteNTP.begin();
}

void update_time(char *datahora){
  clienteNTP.update();
  unsigned long epochTime = clienteNTP.getEpochTime();
  sprintf(datahora, "%02d-%02d-%02d %02d:%02d:%02d", year(epochTime), month(epochTime), day(epochTime), hour(epochTime), minute(epochTime), second(epochTime));
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
  Serial.print("Data Atual: ");
  Serial.println(datahora);
  
  // Envia os dados dos sensores (POST)
  post2api(nomeTemperatura, valorTemperatura, datahora);
  post2api(nomeHumidade, valorHumidade, datahora);

  // Verifica o estado do buzzer na API (GET)
  getBuzzerStatus();

  delay(5000);  
}

// Função para enviar dados (POST)
void post2api(String nomeSensor, String valorSensor, String hora) {
  String URLPath = "/ti/ti016/ti/api/api.php";
  String contentType = "application/x-www-form-urlencoded";
  String body = "nome=" + nomeSensor + "&valor=" + valorSensor + "&hora=" + hora;

  clienteHTTP.post(URLPath, contentType, body);

  int responseStatusCode = clienteHTTP.responseStatusCode();
  String responseBody = clienteHTTP.responseBody();

  if (responseStatusCode == 200) {
    Serial.print(nomeSensor);
    Serial.println(" enviado com sucesso!");
    Serial.println("Resposta: " + responseBody);
  } else {
    Serial.print("Erro ao enviar ");
    Serial.print(nomeSensor);
    Serial.print(". Status: ");
    Serial.println(responseStatusCode);
    Serial.println("Resposta: " + responseBody);
  }

  clienteHTTP.stop(); 
}

// Nova função para ler o estado do Buzzer (GET)
void getBuzzerStatus() {
  // Caso a API precise de um parâmetro na URL para saber que queres o buzzer, 
  // podes mudar para "/ti/ti016/ti/api/api.php?sensor=buzzer"
  String URLPath = "/ti/ti016/ti/api/api.php?nome=buzzer"; 

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