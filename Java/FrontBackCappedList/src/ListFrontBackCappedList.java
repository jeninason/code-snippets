import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

public class ListFrontBackCappedList<T> implements FrontBackCappedListInterface<T> {

	private List<T> list; // initialize to type ArrayList<T> in the ListFrontBackCappedList constructor
	private int capacity = 0;
	
	public ListFrontBackCappedList(int capacity) {
		this.list = new ArrayList<T>();
		this.capacity = capacity;
	}

	public boolean addFront(T newEntry) {
		if (!isFull()) {
			list.add(0, newEntry);
			return true;
		}
		return false;

	}

	public boolean addBack(T newEntry) {
		if (!isFull()) {
			list.add(newEntry);
			return true;
		}
		return false;
	}

	public T removeFront() {

		if (!isEmpty()) {
			return list.remove(0);
		}
		return null;
	}

	public T removeBack() {
		if (!isEmpty()) {
			return list.remove(list.size()-1);
		}
		return null;
	}

	public void clear() {
		list.clear();
	}
	
	public T getEntry(int givenPosition) {
		if (givenPosition >= 0 && givenPosition < list.size()) {
			return list.get(givenPosition);
		}
		return null;
	}

	public int indexOf(T anEntry) {

		return list.indexOf(anEntry);
	}

	public int lastIndexOf(T anEntry) {
		
		return list.lastIndexOf(anEntry);		
	}

	public boolean contains(T anEntry) {
		return list.contains(anEntry);
	}

	public int size() {
		return list.size();
	}

	public boolean isEmpty() {
		return list.isEmpty();
	}

	public boolean isFull() {
		return capacity == list.size();
	}

	@Override
	public String toString() {
		//Expected output: size=5; capacity=10;	[2, 4, 6, 8, 9]
		return "size=" + list.size() + "; capacity=" + this.capacity + ";\t" + list.toString();
	}


}
