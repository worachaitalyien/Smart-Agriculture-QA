"""Train custom 3-class YOLOv8 weights after providing a labelled dataset."""
import argparse
from pathlib import Path
def main():
    p=argparse.ArgumentParser();p.add_argument('--data',required=True);p.add_argument('--epochs',type=int,default=50)
    a=p.parse_args()
    if not Path(a.data).is_file():p.error('Supply an existing labelled dataset YAML')
    from ultralytics import YOLO
    YOLO('yolov8n.pt').train(data=a.data,epochs=a.epochs,imgsz=640,project='runs/detect',name='fruit_web_model')
if __name__=='__main__':main()
