def count_labels(labels):
    counts = dict.fromkeys(('apple', 'mango', 'orange'), 0)
    for label in labels:
        name = str(label).strip().lower()
        if name in counts:
            counts[name] += 1
    return counts
