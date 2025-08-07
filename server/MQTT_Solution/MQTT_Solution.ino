#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <Wire.h>
#include <Seeed_BME280.h>
#include <Digital_Light_TSL2561.h>

#include "secure.h"

#define SEALEVELPRESSURE_HPA (1013.25)

// Jedes Board besitzt eine Seriennummer, die muss hier für jedes Board
// angepasst werden. 
const int BOARDNUMBER=1003;

// Variable für String mit Sensordaten
char sensordata[100];

// wird im Loop für als Hilfsvariable für Wartezeit benötigt
unsigned long lastAction = 0;
// Die Wartezeit im Loop, bis nächste Aktion ausgeführt wird
const int unsigned long intervall = 300000;

// Topics für Senden und Empfangen
const char* topicSend = "ZbW/Monitor";
const char* topicRecieve = "ZbW/Recieve";

// Instanz des Bosch-Sensors erstellen
BME280 bme;
// Instanz des WiFi-Controllers erstellen
WiFiClient espClient;
// Instanz des MQTT-Controllers erstellen
PubSubClient client(espClient);

//Pins der RGB-LED definieren
const int rgb_R = 13;
const int rgb_G = 15;
const int rgb_B = 12;

void setup() {
  Serial.begin(115200);
  delay(10);
  // RGB-LED initialisieren
  initRgbLed();
  // Bosch-Sensor initialisiseren
  initEnvironmentSensor();
  // WiFi starten
  setup_wifi();
  // Broker für Publish initialisieren
  client.setServer(mqtt_server, 1883);
  // Callback-Funktion für empfangene Nachrichten festlegen
  client.setCallback(callback);
  // Lichtsensor initialisieren
  initLightSensor();
}

void setup_wifi() {
  delay(10);
  Serial.println("Verbindung zum WLAN wird hergestellt...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    // Blaue LED aktivieren
    digitalWrite(rgb_B, HIGH);
    Serial.print(".");
    delay(250);
    // Blaue LED deaktivieren
    digitalWrite(rgb_B, LOW);
    delay(250);
  }
  Serial.println("\nVerbunden mit WLAN");
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Verbindung zum MQTT-Broker wird hergestellt...");
    if (client.connect("Node0")) {
      // Topic abonnieren
      client.subscribe(topicRecieve);
      Serial.println("Verbunden");
    } else {
      Serial.print("Fehler, rc=");
      Serial.print(client.state());
      Serial.println(" Neuer Versuch in 5 Sekunden");
      delay(5000);
    }
  }
}

void initLightSensor() {
  TSL2561.init();  // Initialisierung (Seeed Version)
  delay(1000);     // Warte auf stabile Messwerte
  Serial.println("TSL2561 initialisiert (Seeed)");
}


void initEnvironmentSensor() {
  Serial.println("Starte BME280 Sensor...");
  if (!bme.init()) {
    Serial.println("Fehler: BME280 nicht gefunden!");
  }
}

void initRgbLed() {
  pinMode(rgb_R, OUTPUT);
  pinMode(rgb_G, OUTPUT);
  pinMode(rgb_B, OUTPUT);

  digitalWrite(rgb_R, LOW);
  digitalWrite(rgb_G, LOW);
  digitalWrite(rgb_B, LOW);
}

void publishDataAsString() {
  int temperatur = (int)bme.getTemperature();
  int humidity = (int)bme.getHumidity();
  int pressure = (int)bme.getPressure() / 100;
  int lux = (int)TSL2561.readVisibleLux();
  // Daten-String zusammensetzen
  snprintf(sensordata, sizeof(sensordata), "board:%d;temp:%2d;hum:%2d;pres:%2d;lum:%2d", BOARDNUMBER, temperatur, humidity, pressure, lux);
  Serial.println(sensordata);
  // Daten-String an MQTT-Broker senden
  client.publish(topicSend, sensordata);
}

/*
void publishDataSeparately() {
  //Termperatursensor übermitteln
  char temperatur[5];
  itoa(bme.getTemperature(), temperatur, 10);
  client.publish("ZbW/Node-130/Temperatur", temperatur);
  Serial.print("Temperatur: ");
  Serial.print(bme.getTemperature());
  Serial.println(" °C");
  delay(1000);

  //Luftfeuchtigkeit übermitteln
  char humidity[5];
  itoa(bme.getHumidity(), humidity, 10);
  client.publish("ZbW/Node-130/Luftfeuchtigkeit", humidity);
  Serial.print("Luftfeuchtigkeit: ");
  Serial.print(bme.getHumidity());
  Serial.println(" %");
  delay(1000);

  //Luftdruck übermitteln
  char pressure[5];
  itoa(bme.getPressure() / 100, pressure, 10);
  client.publish("ZbW/Node-130/Luftdruck", pressure);
  Serial.print("Luftdruck: ");
  Serial.print(bme.getPressure() / 100);
  Serial.println(" mBar");
  delay(1000);

  //Lichtstärke übermitteln
  char lux[5];
  itoa(TSL2561.readVisibleLux(), lux, 10);
  client.publish("ZbW/Node-130/Lux", lux);
  Serial.print("Lux: ");
  Serial.println(lux);
  delay(1000);
}

*/

/**
    Callback-Funktion wird aufgerufen, wenn Nachrichten über ein
    bestimmtes Topic eintreffen.
*/
void callback(char* topic, byte* payload, unsigned int length) {
  Serial.print("Nachricht empfangen für Topic: ");
  Serial.println(topic);

  // Anlegen eines Zwischenspeichers für den Payload
  char messageBuffer[length + 1];
  // Die Bytes des Payload in den char-Zwischenspeicher kopieren
  memcpy(messageBuffer, payload, length);
  // String terminieren, damit das Ende erkannt wird.
  messageBuffer[length] = '\0';

  // Nun ist Payload ein String
  String message = String(messageBuffer);
  Serial.println(message);

  // LED-Controller aufrufen
  ledControll(message);
}

void ledControll(String state) {
  // Wenn LED aus, dann aktiviere diese
  if (state == "on") {
    digitalWrite(rgb_G, HIGH);
    // Wenn LED an, dann deaktiviere diese
  } else if (state == "off") {
    digitalWrite(rgb_G, LOW);
  }
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
  if (millis() - lastAction >= intervall) {
    publishDataAsString();
    //publishDataSeparately();
    lastAction = millis();
  }
}