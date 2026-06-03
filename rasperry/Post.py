from gpiozero import LED
from time import sleep
import datetime
import requests
import time

led = LED(4)
url = "http://10.20.229.11/ti/api/files/temperatura/valor.txt"
print("Prima ctr+c para sair") 

def post2API(nome, valor):
    agora = datetime.datetime.now()
    payload = {'nome': nome, 'valor': valor, 'hora': agora.strftime("%Y-%m-%d %H:%M:%S")}
    try:
        # Adicionado um timeout para evitar que o script fique preso se a API falhar
        r = requests.post('http://10.20.229.11/ti/api/api.php', data=payload, timeout=5)
        if r.status_code != requests.codes.ok:
            print("Erro na API:\n" + r.text)
    except Exception as e:
        print(f"Falha ao comunicar com a API: {e}")

# O try/except principal envolve o loop inteiro agora
try:
    while True:
        try:
            # Movemos a hora para DENTRO do loop para atualizar sempre
            t = time.localtime() 
            x = requests.get(url, timeout=5)
            
            if x.status_code == requests.codes.ok:
                print("codigo 200")
                print(time.strftime("%Y-%m-%d %H:%M:%S", t) + " - Valor lido: " + x.text.strip())
                
                # Validação segura para evitar craches se o texto não for número
                try:
                    temperatura = float(x.text.strip())
                    
                    if temperatura > 20:
                        print("Vou ligar o LED do RPI")
                        led.on()
                        post2API('led', 1)
                    else:
                        print("Vou desligar o LED do RPI")
                        led.off()
                        post2API('led', 0) # Corrigido para 0
                        
                except ValueError:
                    print("Aviso: O ficheiro não continha um número válido.")
            else:
                print(f"Erro HTTP: {x.status_code}")
                
        # Captura apenas erros de rede, mas NÃO faz break. Deixa o loop tentar de novo.
        except requests.exceptions.RequestException as e:
            print(f"Erro de ligação (tentando novamente em breve): {e}")
            
        # Espera 5 segundos antes da próxima verificação
        sleep(5)

# O Ctrl+C é capturado aqui de forma limpa
except KeyboardInterrupt:
    print("\nA sair do programa por Ctrl+c...")
    
# O finally garante que corre apenas uma vez, no fim de tudo
finally:
    led.off() # Boa prática: garantir que o LED desliga quando o programa fecha
    print("Terminou o programa")