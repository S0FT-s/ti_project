import requests
import time

url = "http://iot.dei.estg.ipleiria.pt/api/api.php?sensor=btc"
t = time.localtime()

while(True):
	x = requests.get(url)
	if(x.status_code == requests.codes.ok):
		print("codigo 200")
		print(time.strftime("%Y-%m-%d %H:%M:%S", t)+" "+ x.text)
	else:
		print("Deu erro")
	time.sleep(5)

