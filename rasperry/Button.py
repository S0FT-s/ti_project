from gpiozero import Button
from signal import pause

# Define que o botao esta ligado ao pino GPIO 17
botao = Button(17)

def acao_pressionar():
    print("Botao pressionado! As cenas estao a acontecer! =)")

def acao_soltar():
    print("Botao solto!")

botao.when_pressed = acao_pressionar
botao.when_released = acao_soltar

print("Programa a correr. Clica no teu botao para testar!")
print("(Pressiona Ctrl+C no teclado para sair do programa)")

pause()
