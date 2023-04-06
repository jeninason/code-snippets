import java.util.*;

public class BinarySearchTreeWithDups<T extends Comparable<? super T>> extends BinarySearchTree<T>
		implements SearchTreeInterface<T>, java.io.Serializable {

	//private static int recursionTimes = 0; // declare this variable for the efficiency tests for recursion
	
	public BinarySearchTreeWithDups() {
		super();
	}

	public BinarySearchTreeWithDups(T rootEntry) {
		super(rootEntry);
		setRootNode(new BinaryNode<T>(rootEntry));
	}

	@Override
	public T add(T newEntry) {
		T result = null;
		if (isEmpty())
			setRootNode(new BinaryNode<T>(newEntry));
		else
			result = addEntryHelperNonRecursive(newEntry);
		return result;
	}

	// THIS METHOD CANNOT BE RECURSIVE.
	private T addEntryHelperNonRecursive(T newEntry) {
		T result = null;
		boolean done = false;
		BinaryNode<T> rootNode = getRootNode();
		
		// need to write as a loop to find the position		
		while (!done) {
			int comparison = newEntry.compareTo(rootNode.getData());
			if (comparison <= 0) {
				if (comparison == 0) {
					result = rootNode.getData();
				}
				//left subtree for equals and lower
				if (rootNode.hasLeftChild()) {
					//keep going, there are more
					rootNode = rootNode.getLeftChild();
				} else {
					rootNode.setLeftChild(new BinaryNode<>(newEntry));
					done = true;
				}
				
			} else { //greater than
				
				if (rootNode.hasRightChild()) {
					rootNode = rootNode.getRightChild();
				} else {
					rootNode.setRightChild(new BinaryNode<>(newEntry));
					done = true;

				}
			}
		}
		

		
		return result; 
	}

	
	public int countEntriesNonRecursive(T target) {
		//int loopTimes=0; // declare this variable for the efficiency tests
		
		int count = 0;
		boolean done = false;
		BinaryNode<T> currentNode = getRootNode();
		while (!done) {
			//loopTimes++; // increment your variable here
			int comparison = target.compareTo(currentNode.getData());
			if (comparison <= 0) {
				if (comparison == 0) {
					count++;
				}
				//left subtree for equals and lower
				if (currentNode.hasLeftChild()) {
					//keep going, there are more
					currentNode = currentNode.getLeftChild();
				} else {
					done = true;
				}
				
			} else { //greater than
				
				if (currentNode.hasRightChild()) {
					currentNode = currentNode.getRightChild();
				} else {
					done = true;

				}
			}
			
			
		}
	    //System.out.println(loopTimes); // print out your variable before you return
	
		return count; 
	}
	
	
	public int countGreaterRecursive(T target) {
		// this initial code is meant as a suggestion to get your started- use it or delete it!
		//recursionTimes = 0;
		int count = 0;
		
		BinaryNode<T> rootNode = getRootNode();
		int counter =  countGreaterRecursiveHelper(rootNode, target, count);
		//System.out.println(recursionTimes); // print out variable before return
		return counter;
	}
    
    private int countGreaterRecursiveHelper(BinaryNode<T> currentNode, T target, int count) {
    	//recursionTimes++; // increment your variable here
		int countLeftTree = 0;
		int countRightTree = 0;
		int compare = target.compareTo(currentNode.getData());

		if (currentNode.hasLeftChild() && compare <= 0) {
		    countLeftTree = countGreaterRecursiveHelper(currentNode.getLeftChild(), target, count);
		}
		if (currentNode.hasRightChild()) {
		    countRightTree = countGreaterRecursiveHelper(
			currentNode.getRightChild(), target, count);
		}

		if (compare < 0) count++;

		return count + countLeftTree + countRightTree;
	}
    
	// THIS METHOD CANNOT BE RECURSIVE.
	public int countGreaterIterative(T target) {
		//int loopTimes=0; // declare this variable for the efficiency tests
		int count = 0;
		BinaryNode<T> rootNode = getRootNode();
		Stack<BinaryNode<T>> nodeStack = new Stack<BinaryNode<T>>();
		nodeStack.push(rootNode);
		
		//The method counts the number of elements in the tree greater than the parameter.
		while (!nodeStack.isEmpty()) { //keep looping until the stack is empty
			//loopTimes++; // increment your variable here 

			BinaryNode<T> currentNode = nodeStack.pop();
			if (currentNode == null) { //nothing to check
				//proceed, there's more on the stack
			} else {
				if (target.compareTo(currentNode.getData()) <= 0) {
					if (!target.equals(currentNode.getData())) { //they don't match, add the counter
						count++;
					}
					//add the left and right to the stack
					nodeStack.push(currentNode.getLeftChild());
					nodeStack.push(currentNode.getRightChild());
				} else {
					//less than, so just add the right node
					nodeStack.push(currentNode.getRightChild());
				}
			}
			
		}
		
		//System.out.println(loopTimes); // print out your variable before you return

		return count;
	}
		
	
	public int countUniqueValues() {
		//int loopTimes=0; // declare this variable for the efficiency tests
		int count = 0;
		boolean done = false;
		BinaryNode<T> rootNode = getRootNode();	
		Set<T> uniqueSet = new HashSet<T>();
		
		Stack<BinaryNode<T>> nodeStack = new Stack<BinaryNode<T>>();
		nodeStack.push(rootNode);
		
		//The method counts the number of elements in the tree greater than the parameter.
		while (!nodeStack.isEmpty()) { //keep looping until the stack is empty
			//loopTimes++; // increment your variable here 

			BinaryNode<T> currentNode = nodeStack.pop();
			if (currentNode == null) { //nothing to check
				//proceed, there's more on the stack
			} else {
				//here add to the set, and then add the children
				uniqueSet.add(currentNode.getData());
				if (currentNode.hasLeftChild()) {
					nodeStack.push(currentNode.getLeftChild());
				}
				if (currentNode.hasRightChild()) {
					nodeStack.push(currentNode.getRightChild());
				}
			}
			
		}
		
//		System.out.println(loopTimes); // print out your variable before you return

		return uniqueSet.size(); 
	}
		
	
	public int getLeftHeight() {
		BinaryNode<T> rootNode = getRootNode();
		if(rootNode==null) {
			return 0;
		} else if(!rootNode.hasLeftChild()) {
			return 0;
		} else {
			return rootNode.getLeftChild().getHeight();
		}
	}

	public int getRightHeight() {
		BinaryNode<T> rootNode = getRootNode();
		if(rootNode==null) {
			return 0;
		} else if(!rootNode.hasRightChild()) {
			return 0;
		} else {
			return rootNode.getRightChild().getHeight();
		}
	}
	

}
