
public class Course {

	/*
	 * Declare instance data variables to represent:
		the course name
		the roster and waitlist as two Student[] variables
		the maximum number of students allowed on the waitlist and on the roster
		any other variables you think are helpful
	 * 
	 */
	String courseName = "";
	private Student[] roster; // how to make an array of objects?
	private Student[] waitlist;
	int maxEnrolled = 0;
	int maxWaitlist = 0;
	
	//Constructor
	public Course(String newCourseName, int newMaxEnrolled, int newMaxWaitlist) {
		courseName = newCourseName;
		maxEnrolled = newMaxEnrolled;
		maxWaitlist = newMaxWaitlist;
		roster = new Student[maxEnrolled];
		waitlist = new Student[maxWaitlist];
		//roster + waitlist arrays are initially empty, but do we need to include them here?		
	}

	//Getters and Setters
	public String getCourseName() {
		return courseName;
	}
	public int getMaxEnrolled() {
		return maxEnrolled;
	}
	public int getMaxWaitlist() {
		return maxWaitlist;
	}
	public int getNumEnrolled() {
		return 0; //placeholder
	}
	public int getNumWaitlist() {
		return 0; //placeholder
	}

	public void setCourseName(String newCourseName) {
		if (newCourseName.length() > 0) {
			courseName = newCourseName;
		} else {
			System.out.println("Please enter a course name");
		}
	}

	public void setMaxEnrolled(int newMaxEnrolled) {
		if (newMaxEnrolled > 0) {
			maxEnrolled = newMaxEnrolled;
		} else {
			System.out.println("Please enter a number greater than zero for max enrolled");
		}
		
	}
	public void setMaxWaitlist(int newMaxWaitlist) {
		if (newMaxWaitlist > 0) {
			maxWaitlist = newMaxWaitlist;
		} else {
			System.out.println("Please enter a number greater than zero for max waitlist");
		}
	}
	
	public String toString() {
		/*
		the name of the course
		the number of students enrolled in the course and the maximum number that can be enrolled
		the roster of enrolled students (all student data for all enrolled students should be included)
		the number of students on the waitlist and the maximum number that can be on the waitlist
		the students on the waitlist (all student data for all waitlisted students should be included)
		note: for full credit, make sure that there are no "nulls" printed with the arrays
		 */
		//THIS IS MISSING LOTS, PLACEHOLDER
		return courseName + "\nMax Enrolled: " + maxEnrolled + "\nMax Roster: " + maxWaitlist;
	}

	public boolean addStudent(Student student) {
		if(student.isTuitionPaid() /*&& notonroster && notonwaitlist*/) {
			/*if(!maxEnrolled){
			  	add student to roster
			  	return true;
			  	} else if(maxEnrolled and !maxWaitlist){
			  		add student to waitlist
			  		return true;
			  	} else {
			  		do not add student (not sure if we need any action here, but we need to else to force it to take one path right?
			  		return false;
			  	}*/
		} else {
			return false;
		}
		/*
		a student is eligible to add the course if:
		they have paid tuition and
		they are not already enrolled on the roster or waitlist
		if a student is eligible to add:
		if there is room on the roster, add the student to the roster
		if the roster is full but there is room on the waitlist, add the student to the waitlist
		if there is no room on the roster or waitlist, do not add the student
		return true or false based on whether the student is added or not
		if a student is added to either the roster or the waitlist, the method should return true		
		 */
		return true; //placeholder
	}
	
	public boolean dropStudent(Student student) {
		/*
		if the student is not on the roster or waitlist, the student cannot be dropped
		if the student is on the waitlist, remove the student from the waitlist
		if the student is on the roster, remove the student from the roster
		since there is now one more space in the class, if the waitlist is not empty, take the first student off the waitlist and add them to the roster; then remove that student from the waitlist (and then shift everyone else up in their waitlist position)
		return true or false based on whether the student is dropped or not
		if a student is dropped from either the roster or the waitlist, the method should return true
		 */
		return true; //placeholder
	}
	
	
	
}
