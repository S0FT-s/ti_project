#include <WiFiNINA.h>
#include <ArduinoHttpClient.h>

char HOST[] = "10.20.228.248";
int PORTO = 80;

WiFiClient clienteWifi;
HttpClient clienteHTTP = HttpClient(clienteWifi, HOST, PORTO);

int temp = 15;

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
}

void loop() {
  clienteHTTP.get("/ti_project/ti/api/api.php?nome=temperatura");
  int statusCode = clienteHTTP.responseStatusCode();
  if(statusCode == 200){
    String response = clienteHTTP.responseBody();
    float sensorTemp = response.toFloat();
    Serial.println(response);

    if(sensorTemp >= temp){
      digitalWrite(LED_BUILTIN, HIGH);
    }else{
      digitalWrite(LED_BUILTIN, LOW);
    }
  }else{
    Serial.println(statusCode);
    Serial.println(" Erro no pedido HTTP");
  }
  delay(5000);
}
