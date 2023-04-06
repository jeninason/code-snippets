
public class LinkedFrontBackCappedList<T> implements FrontBackCappedListInterface<T> {

	private Node head, tail;
	private int numberOfEntries = 0;
	private int capacity = 0;
	
	public LinkedFrontBackCappedList(int capacity) {
		head = null;
		tail = null;
		this.capacity = capacity;
		this.numberOfEntries = 0;
	}


	public boolean addFront(T newEntry) {
		if (!isFull()) {
			Node newNode = new Node(newEntry, head);
			if (isEmpty()) {
				head = newNode;
				tail = newNode;				
			} else {
				head = newNode; 
			}
			//iterate numberOfEntries
			++numberOfEntries;
			return true;
		}
		return false; //it's full
		
	}

	public boolean addBack(T newEntry) {
		if (!isFull()) {
			// check isEmpty 
			Node newNode = new Node(newEntry);
			// since we have the last one, but it's not double, still need to iterate?
			if (isEmpty()) {
				//if it's empty, just add it to the "front" like addFront
				head = newNode;
				tail = newNode;			
			} else {
				//here we need to load the tail
				//also needs to update the tail to the new entry
				tail.next = newNode;
				tail = newNode;				
			}				
			
	        ++numberOfEntries; 
	        return true;
					
		}
		return false; //it's full
	}

	public T removeFront() {
		T result = null;

		//this is untested, just a guess, remove functions tested together
		// check isEmpty
		if (!isEmpty()) {
			result = head.data;
			head = head.next;		
			numberOfEntries--;
		}
		
		return result;
	}

	public T removeBack() {
		// check isEMpty
		// would this be like removing at a given position? 
		// perhaps it's just unlinking the last node. 
		// we'd have to find which node has tail in teh next position, and remove it
		// then change tail to be that one
		
		return null;
	}

	public void clear() {
		tail = null;
		head = null;
		numberOfEntries = 0;
	}

	public T getEntry(int givenPosition) {
		// check is valid position (make a helper function)
		// getNodeAt(givenPosition).data;
		if (givenPosition == 0) {
			return head.data;
		} else if (givenPosition == numberOfEntries-1) {
			return tail.data;
		} else if (givenPosition > 0 && givenPosition < numberOfEntries-1) {
			Node currentNode = head;
			for (int counter = 0; counter < givenPosition; counter++) {
				currentNode = currentNode.next;
			}
			return currentNode.data;
		}
		
		return null;
	}

	public int indexOf(T anEntry) {
		Node currentNode = head;
		int index = -1;
		
		for (int counter = 0; counter < numberOfEntries; counter++) {
			if (currentNode.data.equals(anEntry)) {
				return counter;
			}
			currentNode = currentNode.next;
		}

		return index;
	}

	public int lastIndexOf(T anEntry) {
		// check is empty
		// iterate over node list to compare each item. 
		//If found, save location to variable to return
		Node currentNode = head;
		int index = -1;
		
		for (int counter = 0; counter < numberOfEntries; counter++) {
			if (currentNode.data.equals(anEntry)) {
				index = counter;
			}
			currentNode = currentNode.next;
		}

		return index;
	}

	public boolean contains(T anEntry) {
		//need to double check if this is correct
		
		boolean found = false;
		Node currentNode = head;

		while (!found && (currentNode != null)) {
			if (anEntry.equals(currentNode.data)) {
				found = true;
			} else {
				currentNode = currentNode.next;
			}
		}
		return found;
	}

	public int size() {
		return numberOfEntries;
	}

	public boolean isEmpty() {
		return numberOfEntries == 0;
	}

	public boolean isFull() {
		return numberOfEntries == capacity;
	} 

	@Override
	public String toString() { //this needs to be cleaned up
		//from teh driver: [5, 4, 3, 2, 3, 8, 9]\tsize=7\tcapacity=10\thead=5 tail=9
		String outputString = "[";
		String partOutputString = "";
		if (numberOfEntries == 1) {
			outputString += head.data;
			partOutputString = "\thead=" + head.getData() + " tail=" + tail.getData();
		} else if (!isEmpty()) {
			Node currentNode = head;
			while (currentNode.next != null) {
				outputString += currentNode.data;
				if (currentNode.next != null) {
					outputString += ", ";
				}
				currentNode = currentNode.next;
			}
			//add the last one
			outputString += tail.data;
			partOutputString = "\thead=" + head.getData() + " tail=" + tail.getData();
		}
		outputString += "]\tsize=" + numberOfEntries + "\tcapacity=" + capacity;
		outputString += partOutputString;

		return outputString;
	}




	public class Node {
		public T data; 
		public Node next; 

		private Node(T dataValue) {
			data = dataValue;
			next = null;
		}

		private Node(T dataValue, Node nextNode) {
			data = dataValue;
			next = nextNode;
		}

		private T getData() {
			return data;
		}

		private void setData(T newData) {
			data = newData;
		}

		private Node getNextNode() {
			return next;
		}

		private void setNextNode(Node nextNode) {
			next = nextNode;
		} 
	}
	
}
