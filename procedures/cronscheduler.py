import subprocess
import sys, json, base64
import queue, threading

try:
    data = json.loads(base64.b64decode(sys.argv[1]).decode('utf-8'))
except json.JSONDecodeError as e:
    print('Could not parse JSON')
    print(e.msg)
    sys.exit(1)

def worker():
    while True:
        host = q.get()
        if host is None:
            break

        if host['https']:
            https_command = ' https=On'
        else:
            https_command = ''

        command = 'php ' + data['path'] + ' host=' + host['host'] + ' secret=' + host['secret'] + https_command + ' interval=' + data['interval'] + ' memory_limit=' + data['memory_limit']

        print(command)
        print('------')

        process = subprocess.Popen(command, shell=True, stdout=subprocess.PIPE)
        process.wait()

        q.task_done()

q = queue.Queue()
threads = []

for i in range(6):
    t = threading.Thread(target=worker)
    t.start()
    threads.append(t)

for host in data['hosts']:
    q.put(host)

q.join()

for i in range(6):
    q.put(None)

for t in threads:
    t.join()
