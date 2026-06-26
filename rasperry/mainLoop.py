from gpiozero import Button, LED, Buzzer
from signal import pause
from gpiozero import LED
from time import sleep
import datetime
import requests
import time
import re

print("Prima ctr+c para sair")

# Configuração dos componentes físicos ligados aos pinos GPIO do Raspberry Pi
# Define que o botão da campainha está fisicamente ligado ao pino GPIO 17
campainha = Button(17)
# Define que o botão da luz está fisicamente ligado ao pino GPIO 27
buttonLuz = Button(27)

# Define que o LED que representa a ventoinha está ligado ao pino GPIO 22
led_ventoinha = LED(22)  
# Define que o Buzzer físico do alarme está ligado ao pino GPIO 23
buzzer_alarme = Buzzer(23)

# Define o URL principal da API onde o sistema vai ler e escrever os dados
url = 'https://iot.dei.estg.ipleiria.pt/ti/ti016/ti/api/api.php'
# Captura o tempo local atual (inicialização)
t = time.localtime()

# Função para ler o estado atual de um determinado sensor ou atuador a partir do servidor
def get_sensor(nome_sensor):
    # Constrói o URL com o parâmetro GET contendo o nome do sensor que queremos ler
    url = f"https://iot.dei.estg.ipleiria.pt/ti/ti016/ti/api/api.php?nome={nome_sensor}"
    
    try:
        # Executa o pedido HTTP GET
        resposta = requests.get(url)
        
        # Verifica se o servidor respondeu com sucesso (Código 200 OK)
        if resposta.status_code == 200:
            print(f"[{nome_sensor.upper()}] Status: 200 | Lendo dados...")
            
            # Extrai o código fonte devolvido pela página web (que pode vir sujo com tags HTML)
            codigo_fonte = resposta.text
            
            # Utiliza uma expressão regular (Regex) para procurar e substituir todas as tags HTML por vazio,
            # deixando apenas o valor numérico puro. O strip() remove espaços em branco extras.
            valor_limpo = re.sub(r'<[^>]+>', '', codigo_fonte).strip()
            
            print(f"Valor extraído: {valor_limpo}")
            
            # Converte a string limpa para um número inteiro e devolve-o
            return int(valor_limpo)
        else:
            # Se a resposta do servidor não for 200, informa do erro e assume o valor 0 por segurança
            print(f"[{nome_sensor.upper()}] Erro. Status: {resposta.status_code}")
            return 0
            
    # Captura erros de rede (falta de internet, servidor em baixo, etc)
    except requests.exceptions.RequestException as erro:
        print(f"Falha ao ligar à API: {erro}")
        return 0

# Função para enviar um novo estado ou valor para o servidor (escrever dados)
def post2API(nome, valor):
    # Captura a data e hora exata do sistema no momento em que a função é chamada
    agora = datetime.datetime.now()
    # Prepara o "pacote" de dados (dicionário) com o formato exigido pela API
    payload = {'nome': nome, 'valor': valor, 'hora': agora.strftime("%Y-%m-%d %H:%M:%S")}
    try:
        # Executa o pedido HTTP POST para enviar os dados
        r = requests.post(url, data=payload) 
        # Verifica se ocorreu algum problema no lado do servidor ao processar o POST
        if(r.status_code != requests.codes.ok):
            print("Erro:\n"+r.text)
    except Exception as e:
        # Imprime uma mensagem caso haja falha de conexão na tentativa de envio
        print("Falha ao comunicar")

# Faz uma leitura inicial "vazia" do LED (o retorno não é guardado numa variável aqui)
get_sensor("led")

# Funções de Callbacks (Ações que ocorrem quando os botões físicos são interagidos)

def campainhaTocar():
    print("Botao pressionado! As cenas estao a acontecer! =)")
    # Quando o botão é pressionado, envia o valor 1 (LIGADO) para o sensor "campainha" na base de dados
    post2API("campainha", 1)

def campainhaDesligar():
    print("Botao solto!")
    # Espera 2 segundos antes de registar o fim do toque
    sleep(2)
    # Envia o valor 0 (DESLIGADO) para a base de dados
    post2API("campainha", 0)

def ligarLuz():
    print("Ligar a luz")
    # Quando o segundo botão é pressionado, envia a instrução para ligar o "led"
    post2API("led", 1)

def DesligarLuz():
    print("desligar a luz")
    # Quando o botão é solto, envia a instrução para desligar o "led"
    post2API("led", 0)


# Ciclo infinito que mantém o programa a correr continuamente
while(True):
    try:
        # Atribuição das funções aos eventos dos botões
        # Sempre que o botão for pressionado (when_pressed), invoca a respetiva função
        campainha.when_pressed = campainhaTocar
        campainha.when_released = campainhaDesligar
        buttonLuz.when_pressed = ligarLuz
        buttonLuz.when_released = DesligarLuz
        
        # Faz uma consulta ao servidor para obter o estado mais recente de todos os parâmetros essenciais
        alarme = get_sensor("alarme")
        ventoinha = get_sensor("ventoinha")
        temp_alvo = get_sensor("tAlvo") 
        alarme_desarmado = get_sensor("gatilho_alarme")
        temp = get_sensor("temperatura")

        # Lógica de Controlo da Ventoinha:
        # Se a temperatura alvo for maior que a temperatura atual, OU se a ventoinha foi ligada manualmente (valor 1)
        if temp_alvo > temp or ventoinha:
            # Acende o LED físico ligado ao pino 22 para representar a ventoinha a trabalhar
            led_ventoinha.on()
        else:
            # Caso contrário, desliga o LED da ventoinha
            led_ventoinha.off()

        # Lógica de Controlo do Alarme:
        # Verifica se o sistema está armado (alarme_desarmado == 1) E se o sensor de alerta disparou (alarme == 1)
        if alarme_desarmado and alarme:
            # Aciona fisicamente o componente Buzzer
            buzzer_alarme.on()
            print("Alarme: A TOCAR!")
        else:
            # Desliga o Buzzer se o sistema estiver desarmado ou não houver alerta
            buzzer_alarme.off()
            
        # Adormece o ciclo durante 5 segundos antes de repetir todo o processo de leitura e atualização para não sobrecarregar o servidor
        time.sleep(5)

    # Captura de exceções gerais durante a execução do ciclo
    except Exception as e :
        print(f"Erro: {e}")
        # Interrompe o ciclo infinito e termina o programa
        break

    # Captura a interrupção gerada quando o utilizador prime Ctrl+C na consola
    except KeyboardInterrupt:
        print("A sair do programa por Ctrl+c")
        break
        
    # O bloco finally executa sempre que o try/except termina, útil para limpezas de memória (neste caso está inativo)
    finally:
        pass #print("Terminou o programa")