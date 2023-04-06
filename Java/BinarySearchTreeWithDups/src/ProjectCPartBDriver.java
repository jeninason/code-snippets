import java.io.*;
import java.text.*;
import java.util.*;
import java.time.*;

public class ProjectCPartBDriver {
	
	// adjust how many reports you read in- lower the maxNumberReports if your machine is taking too long!
	// set useAllReports to false if you want to read in the whole data file
	// if use all the reports, set runComparisonC to false or else you'll be waiting a long time!
	private static boolean useAllReports = false;
	private static int maxNumberReports = 40000;
	private static boolean runTestB = true; // should be false if useAllReports=true!
	
	public static void main(String[] args) throws IOException, ParseException {

		List<PoliceReport> reportList = readList(); 
		BinarySearchTreeWithDups<PoliceReport> reportTree;
		
		System.out.println("------------------------------TEST A: TREE BUILT FROM A SORTED LIST, ASCENDING------------------------------");
		Collections.sort(reportList);
		reportTree = buildTreeFromList(reportList);
		processTree(reportTree);

		if(!useAllReports && runTestB) {
			System.out.println("\n\n------------------------------TEST B: TREE BUILT FROM A SORTED LIST, DESCENDING------------------------------");
			Collections.sort(reportList);
			Collections.reverse(reportList);
			reportTree = buildTreeFromList(reportList);
			processTree(reportTree);
		} else if(useAllReports && runTestB) {
			System.out.println("\n\n------------------------------TEST B: TREE BUILT FROM A SORTED LIST, DESCENDING------------------------------");
			System.out.println("***TEST B canceled. I strongly recommend that you set useAllReports to false in order to run Test B!");
		}

		System.out.println("\n\n------------------------------TEST C: TREE BUILT FROM A SHUFFLED LIST------------------------------");
		Collections.shuffle(reportList);
		reportTree = buildTreeFromList(reportList);
		processTreeAndList(reportTree, reportList);

		
		System.out.println("\n\n------------------------------EXTRA CREDIT TEST: COUNT OF UNIQUE VALUES------------------------------");
		// this is set in line 89
		System.out.println("Unique Values: "); 
		if(!useAllReports) {
			if(maxNumberReports!=40000) {
				System.out.println("***ERROR! This test assumes maxNumberReports=40000, not " + maxNumberReports);
				System.out.println("   Change this in line 10 in order to use this test.");
			} else {
				System.out.println("Expected=" + 4664);
				System.out.println("  Actual=" + reportTree.countUniqueValues());
			}
		} else {
			System.out.println("Expected=" + 4681);
			System.out.println("  Actual=" + reportTree.countUniqueValues());			
		}
	}
	private static void processTree(BinarySearchTreeWithDups<PoliceReport> tree) {
		processTreeAndList(tree, null);
	}
	private static void processTreeAndList(BinarySearchTreeWithDups<PoliceReport> tree, List<PoliceReport> list) {
		boolean printList = list==null ? false : true;
		if(printList) {
			System.out.println("Processing tree and list...");
		} else {
			System.out.println("Processing tree...");
		}
		long elapsedTimeTree = 0, elapsedTimeList = 0;
		
		// these represent the earliest date, latest date, two dates in the middle, a high-frequency date, and two dates not in the set
		String[] dateStrings = {"01/1/2003", "10/27/2015", "10/02/2009", "03/23/2010", "03/28/2015", "11/9/2018", "11/9/2000"};
		for (String date : dateStrings) {
			PoliceReport dummyReport = new PoliceReport();
			dummyReport.setDate(date);

			Instant start = Instant.now();
			int reportCountTree = tree.countEntriesNonRecursive(dummyReport);
			long oneTimeTree = Duration.between(start,  Instant.now()).toMillis();
			elapsedTimeTree += oneTimeTree;
			
			start = Instant.now();
			long reportCountList = 0L;
			if(printList) {
				reportCountList = list.stream().filter(report -> report.isOnDate(date)).count();
				/* the above is code that uses Java 8 streams (which we do not cover)
				 * here is what the non-stream code would look like:
				long reportCountL = 0;
				for(PoliceReport report : list) {
					if(report.isOnDate(date)) {
						reportCountL++;
					}
				}
				 */
			}
			long oneTimeList = Duration.between(start,  Instant.now()).toMillis();
			elapsedTimeList += oneTimeList;
			
			System.out.print("\tIncident Date: " + date);
			System.out.print("\tNumber of Incidents: " + reportCountTree);
			System.out.print("\tTree Time: " + oneTimeTree);
			if(printList) {
				System.out.println("  List Time: " + oneTimeList);
			} else {
				System.out.println();
			}
		}
		System.out.println("Processing complete. ");

		System.out.print("Total Time Required: Tree: " + elapsedTimeTree);
		if(printList) {
			System.out.println(" List: " + elapsedTimeList);
			System.out.print((Long.compare(elapsedTimeTree,elapsedTimeList)<0 ? "Tree was faster." : "List was faster."));
		} else {
			System.out.println();
		}
	}
	

	private static List<PoliceReport> readList() throws IOException {
		
		List<PoliceReport> masterReportList = new ArrayList<>();
		
		String fileName = "/home/jnason/Data/home/eclipse-workspace/111c-ProjectC/src/SFPD_Incidents_TheftLarceny.csv";
		Scanner fileScan = new Scanner(new File(fileName));		
		
		fileScan.nextLine(); // read the column headers
			
		int numRecordsReadIn = 0;
		while (fileScan.hasNextLine() &&
				(useAllReports || (!useAllReports && numRecordsReadIn<maxNumberReports)) ){
			numRecordsReadIn++;
			String singleReport = fileScan.nextLine();
			Scanner reportScan = new Scanner(singleReport);
			reportScan.useDelimiter(",");

			// this code assumes the file is perfectly formatted- it does not
			// account for errors in formatting
			PoliceReport report = new PoliceReport(
					Long.parseLong(reportScan.next()), // incident number
					reportScan.next(), // category
					reportScan.next(), // description
					reportScan.next(), // day
					reportScan.next(), // date
					reportScan.next(), // district
					reportScan.next(), // resolution
					reportScan.next() // address
			);
			
			masterReportList.add(report);
		}		
		return masterReportList;
	}

	private static BinarySearchTreeWithDups<PoliceReport> buildTreeFromList(List<PoliceReport> list) {
		Instant start = Instant.now();
		BinarySearchTreeWithDups<PoliceReport> reportTree = new BinarySearchTreeWithDups<PoliceReport>();
		int count = 0;
		for (PoliceReport report : list) {
			reportTree.add(report);
			count++;
			if (count % 10000 == 0) {
				System.out.println("\t...building tree with entry " + count);
			}
		}
		long elapsedTime = Duration.between(start, Instant.now()).toMillis();
		System.out.println("Time required to build tree: " + elapsedTime + " milliseconds");
		System.out.println("Tree Info:");
		System.out.println("\tRoot is " + reportTree.getRootData());
		try {
			System.out.println("\tNumber of nodes: " + reportTree.getRootNode().getNumberOfNodes());
		} catch(StackOverflowError ex) {
			System.out.println("\tNumber of nodes: ***Cannot find the number of nodes! Stack overflow.");
		}
		System.out.println("\tNumber of nodes equal to the root: " + reportTree.countEntriesNonRecursive(reportTree.getRootNode().getData()));
		System.out.println("\tNumber of nodes bigger than root: " + reportTree.countGreaterIterative(reportTree.getRootData()));
		if(reportTree.getRootNode().hasLeftChild()) {
			try {
			System.out.println("\tNumber of nodes in left subtree: " + reportTree.getRootNode().getLeftChild().getNumberOfNodes());
			} catch(StackOverflowError ex) {
				System.out.println("\tNumber of nodes in left subtree: ***Cannot find the number of nodes in the left subtree! Too many nodes! Stack overflow.");
			}
		} else {
			System.out.println("\tNumber of nodes in left subtree: 0");
		}
		if(reportTree.getRootNode().hasRightChild()) {
			try {
				System.out.println("\tNumber of nodes in right subtree: " + reportTree.getRootNode().getRightChild().getNumberOfNodes());
			} catch(StackOverflowError ex) {
				System.out.println("\tNumber of nodes in right subtree: ***Cannot find the number of nodes in the right subtree! Too many nodes! Stack overflow.");
			}
		} else {
			System.out.println("\tNumber of nodes in right subtree: 0");
		}
		try {
			System.out.println("\tLeft height: " + reportTree.getLeftHeight());
		} catch(StackOverflowError ex) {
			System.out.println("\tLeft height: ***Cannot find the left height! Left subtree is too deep and it caused a stack overflow.");
		}
		try {
			System.out.println("\tRight height: " + reportTree.getRightHeight());
		} catch(StackOverflowError ex) {
			System.out.println("\tRight height: ***Cannot find the right height! Right subtree too deep and it caused a stack overflow.");
		}
		System.out.println("Tree build and report complete!\n");
		
		return reportTree;
	}
}
