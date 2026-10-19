"""Lab 10 fruit detector. Simulation is explicit and never passed off as YOLO inference."""
import argparse, json, time
from pathlib import Path
from urllib.request import Request, urlopen
from counts import count_labels

def post(url, payload):
    req=Request(url,data=json.dumps(payload).encode(),headers={'Content-Type':'application/json'})
    with urlopen(req,timeout=5) as response:
        return json.load(response)

def main():
    p=argparse.ArgumentParser()
    p.add_argument('--api',default='http://127.0.0.1:8078/api/store_inventory.php')
    p.add_argument('--weights',default='weights/best.pt')
    p.add_argument('--source',default='0')
    p.add_argument('--interval',type=float,default=3)
    p.add_argument('--simulate',action='store_true',help='Send explicitly synthetic labels; does NOT run YOLO or a camera')
    p.add_argument('--once',action='store_true')
    a=p.parse_args()
    if a.interval<=0:p.error('--interval must be positive')
    if a.simulate:
        while True:
            counts=count_labels(['apple','apple','mango','orange','orange','orange'])
            print('SIMULATION, not camera inference:',counts,post(a.api,counts),flush=True)
            if a.once:return
            time.sleep(a.interval)
    if not Path(a.weights).is_file():
        p.error('Custom fruit weights not found. Supply a trained best.pt with apple/mango/orange classes or use --simulate.')
    import cv2
    from ultralytics import YOLO
    model=YOLO(a.weights)
    supported={str(v).lower() for v in model.names.values()}
    if not {'apple','mango','orange'}<=supported:p.error('Weights must contain all 3 fruit classes; generic COCO weights lack mango.')
    source=int(a.source) if a.source.isdigit() else a.source
    cap=cv2.VideoCapture(source)
    if not cap.isOpened():raise RuntimeError('Cannot open camera/video source')
    last=0
    try:
        while True:
            ok,frame=cap.read()
            if not ok:break
            results=model(frame,verbose=False)
            labels=[model.names[int(box.cls.item())] for r in results for box in r.boxes]
            counts=count_labels(labels)
            if time.monotonic()-last>=a.interval:
                try:print('DETECTION:',counts,post(a.api,counts),flush=True)
                except Exception as e:print('API request failed:',e,flush=True)
                last=time.monotonic()
                if a.once:break
            cv2.imshow('Lab10 fruit detection',results[0].plot())
            if cv2.waitKey(1)&0xFF==ord('q'):break
    finally:
        cap.release();cv2.destroyAllWindows()

if __name__=='__main__':main()
