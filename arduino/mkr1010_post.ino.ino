#include <WiFiNINA.h>
#include <ArduinoHttpClient.h>
#include <DHT.h> 
#include <NTPClient.h>
#include <WiFiUdp.h> //Pré-instalada com o Arduino IDE
#include <TimeLib.h>

char HOST[] = "10.20.228.248";//10.20.228.228 iot.dei.estg.ipleiria.pt
int PORTO = 80;

WiFiClient clienteWifi;
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

int temp = 15;

#define DHTPIN 0 // Pin Digital onde está ligado o sensor
#define DHTTYPE DHT11 // Tipo de sensor DHT
DHT dht(DHTPIN, DHTTYPE); // Instanciar e declarar a class DHT

WiFiUDP clienteUDP;
//Servidor de NTP do IPLeiria: ntp.ipleiria.pt
//Fora do IPLeiria servidor: 0.pool.ntp.org
char NTP_SERVER[] = "ntp.ipleiria.pt";
NTPClient clienteNTP(clienteUDP, NTP_SERVER, 3600);

void post2API(String enviaNome, String enviaValor, String enviaHora){
  String URLPath = "/ti_project/ti/api/api.php";
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


void setup() {

  char SSID[] = "labs";
  char PASS_WIFI[] = "1nv3nt@r2023_IPLEIRIA";
  int status = WL_IDLE_STATUS;

  Serial.begin(115200);
  while (!Serial);
  
  Serial.println("Connecting");
  Serial.println(SSID);
  while(status == WL_IDLE_STATUS){
    status = WiFi.begin(SSID,PASS_WIFI);
    Serial.println(".");
    delay(250);
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

  pinMode(LED_BUILTIN, OUTPUT);
  dht.begin(); 
  clienteNTP.begin();
}

void loop() {
  float temp = dht.readTemperature();
  float hum = dht.readHumidity();

  char datahora[20];
  update_time(datahora);
  Serial.print("Data Atual: ");
  Serial.println(temp);

  post2API(String("temperatura"),String(temp),datahora);
  post2API(String("humidade"),String(hum),datahora);

  delay(5000);
}
