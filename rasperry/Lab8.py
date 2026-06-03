import requests
import time

url = "http://iot.dei.estg.ipleiria.pt/api/api.php?sensor=btc"
t = time.localtime()
print("Prima ctr+c para sair")

while(True):
	try:
		x = requests.get(url)
		if(x.status_code == requests.codes.ok):
			print("codigo 200")
			print(time.strftime("%Y-%m-%d %H:%M:%S", t)+" "+ x.text)
			if(float(x.text.strip()) == 99000):
				print("vou ligar o LED do RPI")
			else:
				print("vou desligar o LED do RPI")
		else:
			print(x.status_code)
		time.sleep(5)
	except Exception as e :
		print(f"Erro: {e}")
		break
	
	except KeyboardInterrupt:
		print("A sair do programa por Ctrl+c")
		break
