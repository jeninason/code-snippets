
public class ArrayFrontBackCappedList<T> implements FrontBackCappedListInterface<T> {

	private T[] list;
	private int numberOfElements;
	
	public ArrayFrontBackCappedList(int capacity) {
		T[] tempList = (T[]) new Object[capacity];
		this.list = tempList;
		this.numberOfElements = 0;
		//for testing PART ONE
//		Object[] objs = {2, 4, 6, 8, 9, null, null, null, null, null};
//        this.list = (T[]) objs;
//        this.numberOfElements = 5;
	}

	public boolean addFront(T newEntry) {
		if (!isFull()) {
			T[] newList = (T[]) new Object[list.length];
			newList[0] = newEntry;
			for (int i=1; i <= numberOfElements; i++) {
				newList[i] = list[i-1];
			}
			list = newList;
			numberOfElements++;
			return true;
		}
		return false;
	}

	public boolean addBack(T newEntry) {
		if (!isFull()) {
			list[numberOfElements] = newEntry;
			numberOfElements++;
			return true;
		}
		return false;
	}

	public T removeFront() {
		T removedItem = null;
		if (!isEmpty()) {
			//copy to a var & loop to move everything up
			removedItem = list[0];
			for (int i = 0; i < numberOfElements -1; i++) {
				list[i] = list[i+1];
			}
			numberOfElements--;
		}
		return removedItem;
	}

	public T removeBack() {
		if (!isEmpty() && validPosition(numberOfElements-1)) {

			int indexOfRemoved = numberOfElements-1;
			T result = list[indexOfRemoved]; // Get entry to be removed
			list[indexOfRemoved] = null;
			numberOfElements--;
			return result;			
		}
		return null;

	}

	public void clear() {
		if (numberOfElements > 0) {
			for (int i = numberOfElements-1; i >= 0; i--) {
				list[i] = null;
			}
			numberOfElements = 0;
		}
		
	}
	
	public T getEntry(int givenPosition) {
		if (validPosition(givenPosition)) {
			return list[givenPosition];
		} else {
			return null;
		}
	}

	public int indexOf(T anEntry) {

		for (int i = 0; i < numberOfElements; i++) {
			if (list[i].equals(anEntry)) {
				return i;
			}
		}
		
		return -1;
	}

	public int lastIndexOf(T anEntry) {
		for (int i = numberOfElements-1; i >= 0; i--) {
			if (list[i].equals(anEntry)) {
				return i;
			}
		}
		return -1;
	}

	public boolean contains(T anEntry) {
		for (int i = 0; i < numberOfElements; i++) {
			if (list[i].equals(anEntry)) {
				return true;
			}
		}
		return false;
	}

	public int size() {
		return numberOfElements;
	}

	public boolean isEmpty() {
		return numberOfElements == 0;
	}

	public boolean isFull() {
		return numberOfElements == this.list.length;
	}

	private boolean validPosition(int position) {
		return position >= 0 && position < numberOfElements;
	}
	
	@Override
	public String toString() {
		String outputString = "size=" + numberOfElements + "; capacity=" + this.list.length + ";\t[";
		//to eliminate the nulls from showing
		for (int i = 0; i < numberOfElements; i++) {
			if (list[i] != null) {
				outputString = outputString + list[i];
				if (i < numberOfElements -1) {
					outputString = outputString + ", ";
				}
			}
		}
			
		outputString = outputString + "]";	
		return outputString;
	}


}
