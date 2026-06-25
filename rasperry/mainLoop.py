from gpiozero import Button, LED, Buzzer
from signal import pause
from gpiozero import LED
from time import sleep
import datetime
import requests
import time
import re

print("Prima ctr+c para sair")

# Define que o botao esta ligado ao pino GPIO 17
campainha = Button(17)
buttonLuz = Button(27)

led_ventoinha = LED(22)  
buzzer_alarme = Buzzer(23)

url = 'https://iot.dei.estg.ipleiria.pt/ti/ti016/ti/api/api.php'
t = time.localtime()

def get_sensor(nome_sensor):
    url = f"https://iot.dei.estg.ipleiria.pt/ti/ti016/ti/api/api.php?nome={nome_sensor}"
    
    try:
        resposta = requests.get(url)
        
        if resposta.status_code == 200:
            print(f"[{nome_sensor.upper()}] Status: 200 | Lendo dados...")
            
            # Pega no código fonte da página (ex: <html><head></head><body>1</body></html>)
            codigo_fonte = resposta.text
            
            # Remove todas as tags HTML (tudo o que estiver entre < e >)
            valor_limpo = re.sub(r'<[^>]+>', '', codigo_fonte).strip()
            
            print(f"Valor extraído: {valor_limpo}")
            
            return int(valor_limpo)
        else:
            print(f"[{nome_sensor.upper()}] Erro. Status: {resposta.status_code}")
            return 0
            
    except requests.exceptions.RequestException as erro:
        print(f"Falha ao ligar à API: {erro}")
        return 0

def post2API(nome,valor):
    agora = datetime.datetime.now()
    payload = {'nome': nome, 'valor': valor, 'hora': agora.strftime("%Y-%m-%d %H:%M:%S")}
    try:
        r = requests.post(url,data=payload) 
        if(r.status_code != requests.codes.ok):
            print("Erro:\n"+r.text)
    except Exception as e:
        print("Falha ao comunicar")

get_sensor("led")
def campainhaTocar():
    print("Botao pressionado! As cenas estao a acontecer! =)")
    #Mudar de buzzer para campainha
    post2API("campainha",1)

def campainhaDesligar():
    print("Botao solto!")
    sleep(2)
    post2API("campainha",0)

def ligarLuz():
    print("Ligar a luz")
    post2API("led",1)

def DesligarLuz():
    print("desligar a luz")
    post2API("led",0)




while(True):
    try:
        campainha.when_pressed = campainhaTocar
        campainha.when_released = campainhaDesligar
        buttonLuz.when_pressed = ligarLuz
        buttonLuz.when_released = DesligarLuz
        
        alarme = get_sensor("alarme")
        ventoinha = get_sensor("ventoinha")
        temp_alvo = get_sensor("tAlvo") #Tenho de criar o ficheiro para guardar os dados nesse ficheiro vai ter o valor txt
        alarme_desarmado = get_sensor("gatilho_alarme")#criar este tbm e alterar na dashboard as cenas
        temp = get_sensor("temperatura")

        if temp_alvo > temp or ventoinha:
            led_ventoinha.on()
        else:
            led_ventoinha.off()
            #codigo para ligar o led da ventoinha

    #O alarme desarmado tem de ser 1 tá armado 0 desarmado
        if alarme_desarmado and alarme:
            buzzer_alarme.on()
            print("Alarme: A TOCAR!")
        else:
            buzzer_alarme.off()
            

        time.sleep(5)

    except Exception as e :
        print(f"Erro: {e}")
        break

    except KeyboardInterrupt:
        print("A sair do programa por Ctrl+c")
        break
    finally:
        pass #print("Terminou o programa")

